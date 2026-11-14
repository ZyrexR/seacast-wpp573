import sys
import unittest
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
from species_contract import normalize_species, to_public_species


class SpeciesContractTests(unittest.TestCase):
    def test_normalize_species_keeps_internal_database_keys(self):
        self.assertEqual(normalize_species("tuna_sirip_kuning"), "madidihang")
        self.assertEqual(normalize_species("cakalang"), "cakalang")
        self.assertEqual(normalize_species("tongkol_krai"), "tongkol")

    def test_to_public_species_maps_internal_keys_without_renaming_legacy_storage(self):
        self.assertEqual(to_public_species("madidihang"), "tuna_sirip_kuning")
        self.assertEqual(to_public_species("cakalang"), "cakalang")
        self.assertEqual(to_public_species("tongkol"), "tongkol_krai")


if __name__ == "__main__":
    unittest.main()
