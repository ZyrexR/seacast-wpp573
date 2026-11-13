"""Canonical public commodity vocabulary for SEACAST inference outputs."""

PUBLIC_SPECIES = (
    "tuna_sirip_kuning",
    "cakalang",
    "tongkol_krai",
)

PUBLIC_LABELS = {
    "tuna_sirip_kuning": "Tuna Sirip Kuning",
    "cakalang": "Cakalang",
    "tongkol_krai": "Tongkol Krai",
}

PUBLIC_TO_INTERNAL = {
    "tuna_sirip_kuning": "madidihang",
    "cakalang": "cakalang",
    "tongkol_krai": "tongkol",
}

_ALIASES = {
    **PUBLIC_TO_INTERNAL,
    "tuna sirip kuning": "madidihang",
    "yellowfin": "madidihang",
    "yellowfin tuna": "madidihang",
    "madidihang": "madidihang",
    "tongkol krai": "tongkol",
    "tongkol": "tongkol",
}


def normalize_species(value: str) -> str:
    """Return the persisted internal key for a supported public species alias."""

    normalized = "_".join(str(value).strip().lower().replace("-", " ").split())
    if normalized in _ALIASES:
        return _ALIASES[normalized]
    raise ValueError(
        f"Unsupported species '{value}'. "
        "Approved public species: tuna_sirip_kuning, cakalang, tongkol_krai."
    )
