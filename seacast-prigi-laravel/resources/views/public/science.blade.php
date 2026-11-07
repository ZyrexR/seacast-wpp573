<x-layouts.public title="Sains & Metodologi Ensemble SDM — SEACAST">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <div class="space-y-3">
            <x-ui.breadcrumbs :items="[['label' => 'Sains & Metodologi', 'url' => null]]" />
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Metodologi Pemodelan Bio-Oseanografi & Evaluasi Model
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
                Platform SEACAST mengintegrasikan teori relung ekologi Hutchinson multidimensi dengan perpaduan tiga algoritma Species Distribution Model (SDM) yang telah diaudit secara saintifik: MaxEnt, Random Forest, dan Generalized Linear Model (GLM).
            </p>
        </div>

        <!-- Formula HSI Card -->
        <x-ui.card title="Formulasi Habitat Suitability Index (HSI) Komposit">
            <div class="p-4 rounded-xl bg-slate-900 text-cyan-300 font-mono text-center text-sm md:text-base my-2">
                HSI<sub>spesies</sub> = w<sub>maxent</sub> &times; P<sub>maxent</sub> + w<sub>rf</sub> &times; P<sub>rf</sub> + w<sub>glm</sub> &times; P<sub>glm</sub>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                Di mana probabilitas kesesuaian dari masing-masing model ternormalisasi dalam rentang [0,000 &ndash; 1,000], dengan jumlah total bobot ensemble &Sigma;w = 1,000.
            </p>
        </x-ui.card>

        <!-- Model Evaluation Summary Table -->
        <x-ui.card title="Hasil Audit Evaluasi Model (AUC & TSS) — Referensi model_evaluations_summary.json">
            <x-ui.table :headers="['Spesies Target', 'Bobot MaxEnt', 'Bobot Random Forest', 'Bobot GLM', 'Ensemble AUC', 'Ensemble TSS', 'Status Metodologis']">
                <tr>
                    <td class="px-5 py-3.5 font-bold">Cakalang (<em>Katsuwonus pelamis</em>)</td>
                    <td class="px-5 py-3.5 font-mono">0.320</td>
                    <td class="px-5 py-3.5 font-mono">0.355</td>
                    <td class="px-5 py-3.5 font-mono">0.325</td>
                    <td class="px-5 py-3.5 font-mono font-bold text-cyan-500">0.795</td>
                    <td class="px-5 py-3.5 font-mono">0.630</td>
                    <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-400">Approved Ensemble</td>
                </tr>
                <tr>
                    <td class="px-5 py-3.5 font-bold">Madidihang (<em>Thunnus albacares</em>)</td>
                    <td class="px-5 py-3.5 font-mono">0.295</td>
                    <td class="px-5 py-3.5 font-mono">0.365</td>
                    <td class="px-5 py-3.5 font-mono">0.340</td>
                    <td class="px-5 py-3.5 font-mono font-bold text-amber-500">0.784</td>
                    <td class="px-5 py-3.5 font-mono">0.606</td>
                    <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-400">Approved Ensemble</td>
                </tr>
                <tr>
                    <td class="px-5 py-3.5 font-bold">Albakor (<em>Thunnus alalunga</em>)</td>
                    <td class="px-5 py-3.5 font-mono font-bold text-rose-500">0.000</td>
                    <td class="px-5 py-3.5 font-mono">0.517</td>
                    <td class="px-5 py-3.5 font-mono">0.483</td>
                    <td class="px-5 py-3.5 font-mono font-bold text-emerald-500">0.860</td>
                    <td class="px-5 py-3.5 font-mono">0.743</td>
                    <td class="px-5 py-3.5 text-xs text-rose-600 dark:text-rose-400 font-semibold">Zero-Weight Override</td>
                </tr>
            </x-ui.table>
        </x-ui.card>

        <!-- Albakor Zero Weight Caveat -->
        <x-ui.alert variant="warning" title="Dokumentasi Khusus: Alasan Bobot MaxEnt Albakor = 0,000">
            Sesuai hasil audit saintifik, bobot MaxEnt untuk Albakor sengaja di-nolkan (w = 0,000) dan dialokasikan hanya pada Random Forest (0,517) dan GLM (0,483). Hal ini dikarenakan evaluasi proxy sirkular MaxEnt untuk Albakor hanya menghasilkan nilai AUC 0,507 (&le; 0,510), yang setara dengan tebakan acak murni. Integritas nilai ini dipertahankan 100% pada Laravel.
        </x-ui.alert>
    </div>
</x-layouts.public>

