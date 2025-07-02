from __future__ import annotations

import argparse
import shutil
import subprocess
import sys
from pathlib import Path
from typing import Iterable, List, Tuple
import tempfile


# ---- ffmpeg‑python ---------------------------------------------------------
try:
    import ffmpeg  # type: ignore
except ImportError:
    sys.exit("[ERROR] Falta la biblioteca 'ffmpeg-python'. Instálala con:\n    pip uninstall ffmpeg  # por si hubiera un paquete erróneo\n    pip install ffmpeg-python")

if not hasattr(ffmpeg, "input"):
    sys.exit(
        "[ERROR] El módulo que se ha importado como 'ffmpeg' no es ffmpeg-python.\n"
        "   1) Desinstala el paquete conflictivo:   pip uninstall ffmpeg\n"
        "   2) Instala el correcto:                pip install ffmpeg-python")

# ─────────────────────────────────────────────────────────────────────────────
# GUI helper (tkinter)
# ─────────────────────────────────────────────────────────────────────────────

try:
    import tkinter as _tk
    from tkinter import filedialog as _fd
except ImportError:
    _tk = None  # type: ignore

# ─────────────────────────────────────────────────────────────────────────────
# Ajustes por defecto
# ─────────────────────────────────────────────────────────────────────────────

DEFAULT_LADDER: List[Tuple[int, int]] = [
    (1080, 5000),
    (720, 3000),
    (480, 1500),
    (360, 800),
]
FRAGMENT_MS_DEFAULT = 10_000  # 10 s

# ─────────────────────────────────────────────────────────────────────────────
# Utilidades
# ─────────────────────────────────────────────────────────────────────────────

def which_or_die(binary: str) -> None:
    path = shutil.which(binary)
    if path is None:
        sys.exit(f"[ERROR] El ejecutable requerido '{binary}' no está en el PATH.\n"
                 f"       Asegúrate de que '{binary}' esté correctamente instalado y accesible desde la terminal.")


def run(cmd: list[str]) -> None:
    print("[CMD]", " ".join(cmd))
    try:
        subprocess.run(cmd, check=True)
    except FileNotFoundError:
        sys.exit(f"[ERROR] No se pudo encontrar el ejecutable: {cmd[0]}\n"
                 f"       Asegúrate de que esté instalado y en tu PATH.")
    except subprocess.CalledProcessError as e:
        sys.exit(f"[ERROR] Error al ejecutar '{cmd[0]}':\n{e}")

# ─────────────────────────────────────────────────────────────────────────────
# Encoders
# ─────────────────────────────────────────────────────────────────────────────

def encode_audio(src: Path, dst: Path) -> None:
    (
        ffmpeg
        .input(str(src))
        .output(
            str(dst),
            acodec="aac",
            audio_bitrate="128k",
            vn=None,
            ar=48_000,
            ac=2,
            movflags="+faststart",
        )
        .overwrite_output()
        .run(quiet=False)
    )


def encode_video_variant(src: Path, dst: Path, height: int, bitrate_kbps: int) -> None:
    (
        ffmpeg
        .input(str(src))
        .filter("scale", "-2", height)
        .output(
            str(dst),
            vcodec="libx264",
            video_bitrate=f"{bitrate_kbps}k",
            maxrate=f"{int(bitrate_kbps * 1.2)}k",
            bufsize=f"{int(bitrate_kbps * 2)}k",
            g=48,
            keyint_min=48,
            preset="fast",
            profile="high",
            level="4.2",
            an=None,
            movflags="+faststart",
        )
        .overwrite_output()
        .run(quiet=False)
    )

# ─────────────────────────────────────────────────────────────────────────────
# Bento4 helpers
# ─────────────────────────────────────────────────────────────────────────────

def fragment_mp4(src: Path, dst: Path, fragment_ms: int) -> None:
    run(["mp4fragment", "--fragment-duration", str(fragment_ms), str(src), str(dst)])



def find_executable_in_same_dir(reference: str, target: str) -> str | None:
    path = shutil.which(reference)
    if path:
        candidate = Path(path).parent / target
        if candidate.exists():
            return str(candidate)
    script_dir = Path(__file__).parent.resolve()
    fallback = script_dir / target
    if fallback.exists():
        return str(fallback)
    return None

def package_with_mp4dash(media_files: Iterable[Path], out_dir: Path, mpd_name: str = "video.mpd") -> None:
    """Usa el nuevo formato de Bento4 mp4dash para crear el manifiesto."""
    mp4dash_cmd = shutil.which("mp4dash")

    if not mp4dash_cmd:
        fallback = find_executable_in_same_dir("mp4fragment", "mp4dash.bat")
        if fallback:
            mp4dash_cmd = fallback
        else:
            sys.exit("[ERROR] No se pudo encontrar 'mp4dash'. Asegúrate de que esté en el PATH o junto a 'mp4fragment' o al script actual.")

    # Crear carpeta temporal para ejecución segura
    with tempfile.TemporaryDirectory() as temp_dir:
        temp_path = Path(temp_dir)
        renamed_files = []

        for original in media_files:
            renamed = temp_path / (original.stem + "_f.mp4")
            shutil.copy2(original, renamed)
            renamed_files.append(str(renamed))

        cmd = [
            mp4dash_cmd,
            f"--mpd-name={mpd_name}",
            "--profiles=on-demand",
        ] + renamed_files

        print("[CMD]", " ".join(cmd))

        try:
            subprocess.run(cmd, check=True, cwd=temp_path, shell=True)
        except subprocess.CalledProcessError as e:
            sys.exit(f"[ERROR] Error al ejecutar mp4dash:\n{e}")

        # Copiar salida generada al directorio de salida deseado
        for item in temp_path.iterdir():
            shutil.move(str(item), str(out_dir))


# ─────────────────────────────────────────────────────────────────────────────
# API principal
# ─────────────────────────────────────────────────────────────────────────────

def generate_dash(
    input_file: str | Path,
    output_dir: str | Path,
    ladder: List[Tuple[int, int]] | None = None,
    segment_duration: int = FRAGMENT_MS_DEFAULT // 1000,
) -> Path:
    """Genera la presentación DASH y devuelve la ruta al MPD."""

    ladder = ladder or DEFAULT_LADDER
    for bin_ in ("ffmpeg", "mp4fragment", "mp4dash"):
        which_or_die(bin_)

    input_path = Path(input_file).expanduser().resolve()
    if not input_path.exists():
        raise FileNotFoundError(input_path)

    out_dir = Path(output_dir).expanduser().resolve()
    out_dir.mkdir(parents=True, exist_ok=True)

    audio_tmp = out_dir / "audio_raw.m4a"
    audio_frag = out_dir / "audio.m4a"
    print(f"[INFO] Extrayendo audio   → {audio_tmp.name}")
    encode_audio(input_path, audio_tmp)
    print(f"[INFO] Fragmentando audio → {audio_frag.name}")
    fragment_mp4(audio_tmp, audio_frag, segment_duration * 1000)
    audio_tmp.unlink(missing_ok=True)

    video_paths: List[Path] = []
    for height, br in ladder:
        raw = out_dir / f"video_{height}p_raw.mp4"
        frag = out_dir / f"video_{height}p.mp4"
        print(f"[INFO] {height}p @ {br}kb/s — codificando…")
        encode_video_variant(input_path, raw, height, br)
        print(f"[INFO] {height}p — fragmentando…")
        fragment_mp4(raw, frag, segment_duration * 1000)
        raw.unlink(missing_ok=True)
        video_paths.append(frag)

    print("[INFO] Empaquetando con mp4dash…")
    package_with_mp4dash([audio_frag] + video_paths, out_dir, segment_duration)

    mpd_path = out_dir / "stream.mpd"
    print(f"[OK] MPD listo: {mpd_path}")
    return mpd_path

# ─────────────────────────────────────────────────────────────────────────────
# CLI / Modo interactivo (GUI)
# ─────────────────────────────────────────────────────────────────────────────

def _gui_prompt() -> tuple[str, str]:
    if _tk is None:
        raise RuntimeError("tkinter no disponible")

    root = _tk.Tk()
    root.withdraw()

    video_path = _fd.askopenfilename(
        title="Selecciona el vídeo a procesar",
        filetypes=[("Archivos de vídeo", "*.mp4 *.mov *.mkv *.avi"), ("Todos", "*.*")],
    )
    if not video_path:
        sys.exit("No se seleccionó ningún vídeo.")

    output_dir = _fd.askdirectory(title="Selecciona la carpeta de salida")
    if not output_dir:
        sys.exit("No se seleccionó carpeta de salida.")

    root.destroy()
    return video_path, output_dir


def _text_prompt() -> tuple[str, str]:
    try:
        inp = input("Ruta del vídeo a procesar: ").strip()
        out = input("Carpeta donde guardar la presentación DASH: ").strip()
        return inp, out
    except KeyboardInterrupt:
        print("\nInterrumpido por el usuario.")
        sys.exit(1)


def main() -> None:
    parser = argparse.ArgumentParser(add_help=False)
    parser.add_argument("input", nargs="?", help="Fichero de vídeo fuente")
    parser.add_argument("output", nargs="?", help="Carpeta de salida")
    args, _ = parser.parse_known_args()

    if args.input and args.output:
        generate_dash(args.input, args.output)
    else:
        if _tk is not None:
            print("No se suministraron argumentos; abriendo cuadros de diálogo…")
            inp, out = _gui_prompt()
        else:
            print("tkinter no disponible; modo texto interactivo.\n")
            inp, out = _text_prompt()
        generate_dash(inp, out)


if __name__ == "__main__":
    main()
