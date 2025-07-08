#!/usr/bin/env python3
"""
Packaging DASH (GUI version)
============================

Flujo básico:
1. **solo_audio**   – extrae el audio intacto (`-c:a copy`) a `audio_solo.mp3`.
2. **encode_video** – crea versiones H‑264 en tres resoluciones × tres CRF.
3. **fragment**     – fragmenta audio y vídeos con `mp4fragment` (10 s).
4. **mp4dash**      – empaqueta todo en un `video.mpd` (perfil *on‑demand*).

Cómo usar
---------
• **Modo gráfico:** simplemente ejecuta `python script.py` y elige vídeo y
  carpeta con los diálogos tkinter.
• **Modo CLI:**   `python script.py <video.mp4> <carpeta_salida>`

Requisitos: ffmpeg, mp4fragment, mp4dash (Bento4) en el PATH + ffmpeg‑python.
"""

import sys, subprocess
import argparse
from pathlib import Path
import ffmpeg  # pip install ffmpeg-python


AUDIO_NAME = "audio.m4a"  # nombre fijo para la extracción
FRAG = "/opt/bento4/Bento4-SDK-1-6-0-641.x86_64-unknown-linux/bin/mp4fragment"
DASH = "/opt/bento4/Bento4-SDK-1-6-0-641.x86_64-unknown-linux/bin/mp4dash"
MPD  = "output/video.mpd"
def run(cmd, cwd=None):
    print("»", " ".join(cmd))
    result = subprocess.run(
        cmd,
        cwd=str(cwd) if cwd else None,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True
    )
    if result.returncode:
        print(result.stdout)        # esto va al .txt
        raise RuntimeError(f"Falló: {' '.join(cmd)}")

# ──────────────────── PASO 1: audio intacto ─────────────────────

def solo_audio(src: Path, out_dir: Path) -> Path:
    """Extrae la pista de audio y la guarda como AAC en un contenedor M4A.
    Se codifica a 128 kb/s para garantizar compatibilidad con mp4dash."""
    audio_path = out_dir / AUDIO_NAME
    (
        ffmpeg
        .input(str(src))
        .output(
            str(audio_path),
            vn=None,               # sin vídeo
            acodec="aac",         # codificar a AAC
            audio_bitrate="128k",
            ar=48_000,
            ac=2,
        )
        .overwrite_output()
        .run(quiet=False)
    )
    return audio_path

# ──────────────────── PASO 2: vídeos H‑264 ──────────────────────

def encode_video(src: Path, out_dir: Path):
    resoluciones = ["960:540", "1280:720", "1920:1080"]
    calidades    = ["20", "30", "40"]
    for res in resoluciones:
        w, h = res.split(":")
        for crf in calidades:
            outfile = out_dir / f"v{w}x{h}_{crf}.mp4"
            (
                ffmpeg.input(str(src))
                      .filter("scale", w, h)
                      .output(
                          str(outfile),
                          vcodec="libx264",
                          crf=crf,
                          g=24,
                          bf=2,
                          flags="+cgop",
                          an=None,
                      )
                      .overwrite_output()
                      .run(quiet=False)
            )

# ──────────────────── PASO 3: fragmentación ────────────────────

def fragment(audio_m4a: Path, video_dir: Path, fragment_ms: int = 10):
    audio_out = video_dir / f"{audio_m4a.stem}_f.mp4"
    run([FRAG, "--fragment-duration", str(fragment_ms), str(audio_m4a), str(audio_out)])
    for mp4 in video_dir.glob("*.mp4"):
        if mp4.name.endswith("_f.mp4") or mp4 == audio_out:
            continue
        out_mp4 = mp4.with_stem(mp4.stem + "_f")
        run([FRAG, "--fragment-duration", str(fragment_ms), str(mp4), str(out_mp4)])

# ──────────────────── PASO 4: empaquetar DASH ───────────────────

def empaquetar_digital(out_dir: Path):
    inputs = sorted(str(f) for f in out_dir.glob("*_f.mp4"))
    if not inputs:
        sys.exit("No hay *_f.mp4 para mp4dash.")
    run([
        DASH,
        "--force",
        "--use-segment-timeline",
        "--profiles=on-demand",
        f"--output-dir={out_dir}/output",
        "--mpd-name=video.mpd",    
        *inputs
    ], cwd=out_dir)

# ───────────────────────── MAIN script ──────────────────────────

def main():
    parser = argparse.ArgumentParser(description="Procesa vídeo a MPEG‑DASH (.mpd)")
    parser.add_argument("input", type=Path, help="Ruta del vídeo de entrada")
    parser.add_argument("output", type=Path, help="Directorio donde guardar el paquete DASH")
    args = parser.parse_args()

    in_path: Path = args.input.expanduser().resolve()
    out_dir: Path = args.output.expanduser().resolve()

    if not in_path.is_file():
        sys.exit(f"❌ Archivo de entrada no encontrado: {in_path}")

    out_dir.mkdir(parents=True, exist_ok=True)
    

    # Ejecución de pasos
    audio_m4a = solo_audio(in_path, out_dir)
    encode_video(in_path, out_dir)
    fragment(audio_m4a, out_dir)
    empaquetar_digital(out_dir)

    mpd_path = out_dir / MPD
    if not mpd_path.exists():
        sys.exit(f"No se generó el archivo MPD esperado: {mpd_path}")

    print(f"MPD generado: {mpd_path}")

if __name__ == "__main__":
    main()
