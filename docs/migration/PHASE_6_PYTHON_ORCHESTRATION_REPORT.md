# SEACAST PRIGI V3 — PHASE 6
# PYTHON SCIENTIFIC ENGINE ORCHESTRATION & SCHEDULER REPORT

**Document ID:** SEACAST-DOC-P6-ORCH-01  
**Phase:** 6 — Python Scientific Engine Orchestration  
**Release Target:** v3.0-rc1  
**Timestamp:** 2026-11-04T20:45:45+07:00  
**Author:** Senior Laravel Architect & Scientific Software Auditor  
**Classification:** Core Implementation Report  

---

## 1. EXECUTIVE SUMMARY

Phase 6 automates the execution and lifecycle management of the Python Scientific Ensemble SDM Engine within the Laravel 13 backend.

Rather than relying on unmonitored cron jobs or fragile ad-hoc shell scripts, SEACAST PRIGI V3 incorporates:
1. **Artisan Console Command** (`php artisan seacast:infer-daily`): Provides an auditable CLI interface with argument validation, process spawning, telemetry reporting, and cache management.
2. **Queue Job Orchestration** (`App\Jobs\ProcessDailyInferenceJob`): Manages asynchronous background inference with 3 retries, exponential backoff, and 180s timeout limits.
3. **Automated Scheduler** (`routes/console.php`): Coordinates daily automated ingest and computation at **04:00 WIB** (`Asia/Jakarta`) with overlap prevention (`withoutOverlapping()`).
4. **Cache Invalidation:** Automatically flushes date-specific and global prediction cache tags in `GeospatialCacheManager` upon computation completion.

---

## 2. ORCHESTRATION PIPELINE ARCHITECTURE

```
[ Laravel Scheduler: 04:00 WIB (Asia/Jakarta) ]
                     |
                     v
   [ Artisan Command: seacast:infer-daily ]
   (or Queued: ProcessDailyInferenceJob)
                     |
                     v
   [ PythonComputationBridge (PHP 8.3) ]
                     |
        (Array-based command execution)
        (Sanitized args: --date, --limit)
                     |
                     v
   [ Python 3.14 Runtime: bridge_inference_runner.py ]
         - scikit-learn 1.9.1
         - Locked baseline weights
         - 8,129 canonical cells
                     |
        (Pure JSON to stdout / Exit 0)
                     |
                     v
   [ Output Parsing & DTO Construction ]
   (App\Infrastructure\Python\DTO\PythonComputationResult)
                     |
                     +---> [ Log Telemetry & Metrics ]
                     |
                     +---> [ GeospatialCacheManager::invalidateDate() ]
```

---

## 3. RELIABILITY & SECURITY MEASURES

1. **Process Isolation:** The Python runtime executes as a decoupled child process without shell interpolation, eliminating shell injection vectors.
2. **Strict Whitelisting:** Only pre-approved scripts (`scripts/bridge_inference_runner.py`, `scripts/daily_inference.py`, `scripts/audit_year_monthly_data.py`) can be triggered by the bridge.
3. **Cache Synchronization:** The geospatial cache layer is synchronized immediately following inference, guaranteeing that web and API clients never serve stale data.
4. **Comprehensive Test Coverage:** Verified by `DailyInferenceCommandTest` and `InferenceQueueJobTest` (5 tests, 10 assertions, 100% PASS).

