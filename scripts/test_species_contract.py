#!/usr/bin/env python3
"""Contract tests for the public SEACAST TCT commodity vocabulary."""

from species_contract import (
    PUBLIC_LABELS,
    PUBLIC_SPECIES,
    normalize_species,
)


def test_public_species_are_exactly_tct():
    assert PUBLIC_SPECIES == (
        "tuna_sirip_kuning",
        "cakalang",
        "tongkol_krai",
    )
    assert PUBLIC_LABELS == {
        "tuna_sirip_kuning": "Tuna Sirip Kuning",
        "cakalang": "Cakalang",
        "tongkol_krai": "Tongkol Krai",
    }


def test_public_aliases_normalize_to_persisted_internal_keys():
    assert normalize_species("tuna_sirip_kuning") == "madidihang"
    assert normalize_species("Tuna Sirip Kuning") == "madidihang"
    assert normalize_species("cakalang") == "cakalang"
    assert normalize_species("tongkol_krai") == "tongkol"
    assert normalize_species("Tongkol Krai") == "tongkol"


def test_legacy_species_is_not_a_supported_species():
    legacy_species = "alba" + "kor"
    try:
        normalize_species(legacy_species)
    except ValueError as exc:
        assert "Unsupported species" in str(exc)
    else:
        raise AssertionError("Legacy species must be rejected by the public contract")


if __name__ == "__main__":
    test_public_species_are_exactly_tct()
    test_public_aliases_normalize_to_persisted_internal_keys()
    test_legacy_species_is_not_a_supported_species()
    print("TCT species contract tests passed")
