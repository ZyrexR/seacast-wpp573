<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendComponentsTest extends TestCase
{
    public function test_design_system_showroom_renders_all_16_ui_components(): void
    {
        $response = $this->get('/design-system');

        $response->assertStatus(200);

        // Verify Design System Showroom headers
        $response->assertSee('Katalog 16 Komponen UI');

        // 1. Alert
        $response->assertSee('1. Alert (4 Varian)');
        $response->assertSee('Integritas Basis Data Valid');

        // 2. Badge
        $response->assertSee('2. Badge');
        $response->assertSee('Z1 Inshore PFG');
        $response->assertSee('Z5 Bycatch Risk');

        // 3. Button
        $response->assertSee('3. Button');
        $response->assertSee('Primary');
        $response->assertSee('Large Size');

        // 4. Input
        $response->assertSee('Koordinat Bujur');
        $response->assertSee('Threshold Kesesuaian HSI');

        // 5. Card, 12. Skeleton, 13. StatCard
        $response->assertSee('Total Sel Terkoneksi');
        $response->assertSee('8.129');

        // 6. Table
        $response->assertSee('14. Table Reusable');
        $response->assertSee('CELL_0001');
        $response->assertSee('CELL_0042');

        // 7. Tabs & 11. Tooltip
        $response->assertSee('Arahkan kursor ke sini');

        // 9. Modal
        $response->assertSee('Buka Modal Dialog');

        // 14. ScientificLegend
        $response->assertSee('Skala Kesesuaian HSI');

        // 15. MapToolbar
        $response->assertSee('Reset ke PPN Prigi Homeport');

        // 10. EmptyState & 16. ErrorState
        $response->assertSee('Tidak Ada Tangkapan Tercatat');
        $response->assertSee('Koneksi Satelit Terputus');
    }

    public function test_public_layout_includes_vite_assets_and_navigation(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('SEACAST');
        $response->assertSee('Scientific Ocean Intelligence');
        $response->assertSee('build/assets/app-');
        $response->assertSee('Beranda');
        $response->assertSee('Tentang SEACAST');
        $response->assertSee('Sains & Metodologi', false);
        $response->assertSee('Data & Dokumentasi', false);
        $response->assertSee('Login');
        $response->assertDontSee('Masuk Platform');
        $response->assertDontSee('Akses Dashboard');
        $response->assertDontSee('Katalog Komponen');
        $response->assertSee('favicon.ico');
        $response->assertSee('assets/branding/seacast-');
    }

    public function test_brand_logo_component_renders_master_and_mark_variants(): void
    {
        $viewFull = $this->blade('<x-ui.brand-logo variant="full" size="md" />');
        $viewFull->assertSee('assets/branding/seacast-logo-light.png');
        $viewFull->assertSee('assets/branding/seacast-logo-dark.png');

        $viewMark = $this->blade('<x-ui.brand-logo variant="mark" size="sm" />');
        $viewMark->assertSee('assets/branding/seacast-symbol-light.png');
        $viewMark->assertSee('assets/branding/seacast-symbol-dark.png');

        $viewHero = $this->blade('<x-ui.brand-logo variant="hero" theme="dark" />');
        $viewHero->assertSee('assets/branding/seacast-logo-dark.png');

        $viewHorizontal = $this->blade('<x-ui.brand-logo variant="horizontal" size="sm" />');
        $viewHorizontal->assertSee('assets/branding/seacast-logo-light.png');
        $viewHorizontal->assertSee('assets/branding/seacast-logo-dark.png');
        $viewHorizontal->assertSee('SEACAST');
    }
}
