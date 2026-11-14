"""Execute the Colab notebook locally with the same cells and project data."""

import json
import os
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
NOTEBOOK = ROOT / "notebooks" / "SEACAST_TCT_HYBRID_MODELING_2015_2025.ipynb"


def main() -> None:
    os.environ["SEACAST_PROJECT_ROOT"] = str(ROOT)
    qgis_proj = Path(r"C:\Program Files\QGIS 4.0.3\share\proj")
    if (qgis_proj / "proj.db").exists():
        os.environ["PROJ_LIB"] = str(qgis_proj)
        os.environ["PROJ_DATA"] = str(qgis_proj)
    os.chdir(ROOT)
    notebook = json.loads(NOTEBOOK.read_text(encoding="utf-8"))
    namespace = {"__name__": "__main__"}
    for index, cell in enumerate(notebook["cells"]):
        if cell["cell_type"] != "code":
            continue
        code = "".join(line for line in cell["source"] if not line.lstrip().startswith("!"))
        print(f"Executing notebook cell {index}", flush=True)
        exec(compile(code, f"{NOTEBOOK.name}:cell-{index}", "exec"), namespace)


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(f"Notebook execution stopped: {exc}", file=sys.stderr, flush=True)
        raise
