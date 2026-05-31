# Crée upload-keystore.jks dans android/ (à lancer une seule fois).
# Ensuite : copier key.properties.example → key.properties et remplir les mots de passe.

$ErrorActionPreference = "Stop"
$androidDir = Join-Path $PSScriptRoot ".." "android"
$keystore = Join-Path $androidDir "upload-keystore.jks"

if (Test-Path $keystore) {
    Write-Host "Keystore existe déjà : $keystore"
    exit 0
}

$keytool = Get-Command keytool -ErrorAction SilentlyContinue
if (-not $keytool) {
    Write-Error "keytool introuvable. Installez le JDK (Android Studio inclut souvent keytool)."
}

Push-Location $androidDir
try {
    & keytool -genkey -v `
        -keystore upload-keystore.jks `
        -keyalg RSA -keysize 2048 -validity 10000 `
        -alias upload `
        -storetype JKS
    Write-Host "Keystore créé : $keystore"
    Write-Host "Copiez key.properties.example vers key.properties et renseignez les mots de passe."
} finally {
    Pop-Location
}
