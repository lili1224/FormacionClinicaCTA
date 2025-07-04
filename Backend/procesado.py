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
from pathlib import Path
import ffmpeg  # pip install ffmpeg-python

# GUI -------------------------------------------------------------
try:
    import tkinter as _tk
    from tkinter import filedialog as _fd
except ImportError:
    _tk = None

AUDIO_NAME = "audio.m4a"  # nombre fijo para la extracción
FRAG = "mp4fragment"
DASH = "mp4dash.bat"
MPD  = "output/video.mpd"
def run(cmd, cwd=None):
    subprocess.run(cmd, check=True, cwd=str(cwd) if cwd else None)
# ───────────────────────── GUI helper ────────────────────────────

def _gui_inputs() -> tuple[str, str]:
    if _tk is None:
        raise RuntimeError("tkinter no disponible")
    root = _tk.Tk(); root.withdraw()
    vid = _fd.askopenfilename(title="Vídeo a procesar",
                              filetypes=[("Vídeo", "*.mp4 *.mov *.mkv *.avi"), ("Todos", "*.*")])
    if not vid:
        sys.exit("No se seleccionó vídeo.")
    out = _fd.askdirectory(title="Carpeta de salida")
    if not out:
        sys.exit("No se seleccionó carpeta de salida.")
    root.destroy()
    return vid, out

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
        sys.exit("No se encontraron archivos fragmentados *_f.mp4 para mp4dash.")
    run([DASH, "--force", "--use-segment-timeline", "--mpd-name=video.mpd", "--profiles=on-demand", *inputs], cwd=out_dir)
    print("DASH listo →", out_dir / MPD)

# ───────────────────────── MAIN script ──────────────────────────

def main():
    if len(sys.argv) == 1:  # GUI
        if _tk is None:
            sys.exit("tkinter no disponible; use argumentos en CLI.")
        src_path, out_dir = map(Path, _gui_inputs())
    elif len(sys.argv) == 3:
        src_path, out_dir = Path(sys.argv[1]), Path(sys.argv[2])
    else:
        print("Uso GUI:   python script.py\nUso CLI:   python script.py <video.mp4> <carpeta>"); return

    out_dir.mkdir(parents=True, exist_ok=True)

    # Ejecución de pasos
    audio_mp3 = solo_audio(src_path, out_dir)
    encode_video(src_path, out_dir)
    fragment(audio_mp3, out_dir)
    empaquetar_digital(out_dir)

if __name__ == "__main__":
    main()
