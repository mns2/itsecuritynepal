<#
  Builds a ready-to-upload release of the site so the server needs only PHP (no Node, no npm, optionally no Composer).

  Usage (from the project folder, in PowerShell):
      powershell -ExecutionPolicy Bypass -File scripts\make-release.ps1

  Output: release\itsn-laravel-<date-time>.zip containing the app, vendor/ (production packages only),
  compiled CSS/JS (public/build) and the admin panel assets. Upload it, unzip it, add the server's .env.
  Never included: .env, node_modules, public/hot, the local SQLite database, logs, caches, tests.
#>
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

$stamp = Get-Date -Format 'yyyyMMdd-HHmm'
$stage = Join-Path $root "release\stage"
$zip = Join-Path $root "release\itsn-laravel-$stamp.zip"

function Step($text) { Write-Host "`n==> $text" -ForegroundColor Cyan }
function Run($exe, [string[]]$arguments) {
    & $exe @arguments
    if ($LASTEXITCODE -ne 0) { throw "$exe $($arguments -join ' ') failed (exit $LASTEXITCODE)" }
}

Step 'Compiling CSS and JavaScript (npm run build)'
if (Test-Path public\hot) { Remove-Item public\hot -Force }   # left behind by "npm run dev"; would break the live site
if (-not (Test-Path node_modules)) { Run npm @('ci') }
Run npm @('run', 'build')

Step 'Copying files to a clean staging folder'
if (Test-Path $stage) { Remove-Item $stage -Recurse -Force }
New-Item -ItemType Directory -Force $stage | Out-Null
$excludeDirs = @('node_modules', 'vendor', 'release', 'tests', '.git', '.idea', '.vscode', '.claude')
& robocopy $root $stage /E /NFL /NDL /NJH /NJS /NP `
    /XD ($excludeDirs | ForEach-Object { Join-Path $root $_ }) `
        (Join-Path $root 'storage\logs') (Join-Path $root 'storage\framework') (Join-Path $root 'storage\app\private') (Join-Path $root 'public\storage') `
    /XF .env .env.backup .env.production database.sqlite hot .phpunit.result.cache phpunit.xml | Out-Null
if ($LASTEXITCODE -ge 8) { throw "robocopy failed (exit $LASTEXITCODE)" }

# Empty runtime folders Laravel expects to exist and be writable
foreach ($d in 'storage\logs', 'storage\framework\cache\data', 'storage\framework\sessions', 'storage\framework\views', 'storage\app\private', 'bootstrap\cache') {
    New-Item -ItemType Directory -Force (Join-Path $stage $d) | Out-Null
    Set-Content -Path (Join-Path $stage "$d\.gitignore") -Value "*`n!.gitignore" -Encoding ascii
}
Get-ChildItem (Join-Path $stage 'bootstrap\cache') -Filter *.php | Remove-Item -Force

Step 'Installing production PHP packages (composer install --no-dev)'
Push-Location $stage
try {
    Copy-Item (Join-Path $root '.env.example') '.env'      # temporary, so artisan can boot during composer scripts
    Run composer @('install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--no-progress')
    Run php @('artisan', 'filament:assets')
    Remove-Item '.env' -Force
    Get-ChildItem 'bootstrap\cache' -Filter *.php | Remove-Item -Force   # caches are rebuilt on the server
} finally { Pop-Location }

if (-not (Test-Path (Join-Path $stage 'public\build\manifest.json'))) { throw 'public/build/manifest.json missing: build failed?' }
if (Test-Path (Join-Path $stage 'public\hot')) { throw 'public/hot must not be shipped' }

Step 'Creating zip'
# Written entry by entry with forward-slash paths (Compress-Archive in PowerShell 5 writes backslashes,
# which Linux / cPanel unzip turns into broken file names).
# Not tar.exe: it stores Windows files as 777/666 (world-writable), and LiteSpeed / suPHP refuse to run
# such PHP files, so every page returns 403. Entries written here carry no Unix mode, so the server's
# unzip applies its normal defaults (folders 755, files 644).
Add-Type -AssemblyName System.IO.Compression, System.IO.Compression.FileSystem
$archive = [System.IO.Compression.ZipFile]::Open($zip, [System.IO.Compression.ZipArchiveMode]::Create)
try {
    $prefix = $stage.TrimEnd('\').Length + 1
    foreach ($item in Get-ChildItem $stage -Recurse -Force) {
        $name = $item.FullName.Substring($prefix).Replace('\', '/')
        if ($item.PSIsContainer) {
            [void]$archive.CreateEntry("$name/")
        } else {
            [void][System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive, $item.FullName, $name, [System.IO.Compression.CompressionLevel]::Optimal)
        }
    }
} finally { $archive.Dispose() }
Remove-Item $stage -Recurse -Force
$size = '{0:N1} MB' -f ((Get-Item $zip).Length / 1MB)
Write-Host "`nRelease ready: $zip ($size)" -ForegroundColor Green
Write-Host 'On the server: unzip, create .env, then run: php artisan migrate --force; php artisan storage:link; php artisan optimize; php artisan filament:optimize'
