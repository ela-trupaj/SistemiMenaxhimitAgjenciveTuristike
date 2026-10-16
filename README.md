# Sistemi për Menaxhimin e Agjencive Turistike

## Raporti 1 – Projekti

### 1. Ideja e projektit

Ideja e këtij projekti është zhvillimi i një sistemi për menaxhimin e agjencive turistike, i cili mundëson menaxhimin e destinacioneve turistike, ofertave, rezervimeve dhe pagesave në një platformë të vetme. Qëllimi është të lehtësohet puna e stafit të agjencisë, të zvogëlohet përdorimi i proceseve manuale dhe të organizohen më mirë të dhënat dhe aktivitetet e përditshme.

### 2. Përdoruesit kryesorë

- **Stafi i agjencisë:** Menaxhon destinacionet, ofertat dhe rezervimet.
- **Administratorët:** Menaxhojnë pagesat dhe raportet financiare.
- **Klientët:** Rezervojnë oferta dhe shohin historikun e tyre.

### 3. Kërkesat funksionale

- Përdoruesi i autentikuar (stafi) mund të shtojë destinacione turistike me emër, përshkrim, çmim dhe datë.
- Administratori mund të krijojë oferta turistike duke zgjedhur destinacionin, periudhën dhe çmimin.
- Klienti i regjistruar mund të bëjë rezervim duke zgjedhur datën e nisjes dhe numrin e personave.
- Sistemi mundëson anulimin e rezervimit nga klienti ose stafi.
- Administratori menaxhon pagesat dhe gjeneron raporte financiare mujore.
- Klienti mund të shohë historikun e rezervimeve të tij.
- Sistemi dërgon njoftime automatike me email për konfirmimin e rezervimit dhe pagesës.

### 4. Kërkesat jofunksionale

- Fjalëkalimet ruhen vetëm në formë të hash-uar dhe endpoint-et private kërkojnë autentikim.
- Ndërfaqja duhet të jetë e thjeshtë dhe e përdorshme edhe në ekran mobil.
- Sistemi duhet të përpunojë rezervime pa vonesa të dukshme (p.sh. të paktën 50 rezervime njëkohësisht).
- Platforma duhet të jetë e aksesueshme 99% të kohës gjatë vitit.
- Sistemi duhet të mbështesë rritjen e numrit të përdoruesve pa ndryshime të mëdha në arkitekturë.
- Të dhënat personale të klientëve ruhen dhe përpunohen sipas politikave të mbrojtjes së të dhënave.

### 5. Dizajni teknik

**Frontend:**  
HTML, CSS dhe JavaScript për ndërtimin e ndërfaqes dhe dizajnin responsiv për përdorim në desktop dhe pajisje mobile.

**Backend:**  
PHP për autentifikimin, menaxhimin e roleve, destinacioneve, ofertave, rezervimeve, pagesave dhe njoftimeve automatike.

**Database:**  
MySQL për ruajtjen e të dhënave dhe relacioneve ndërmjet tyre.

### 6. Struktura e databazës

- **users:** Llogaritë e përdoruesve (email, hash i fjalëkalimit dhe roli).
- **destinations:** Destinacionet turistike (emër, përshkrim, çmim dhe datë).
- **offers:** Ofertat e lidhura me destinacionet (periudhë, çmim dhe detaje).
- **reservations:** Rezervimet e klientëve (datë nisjeje, numër personash dhe status).
- **payments:** Pagesat e rezervimeve (shuma, data dhe statusi).
- **notifications:** Njoftimet automatike për konfirmimet e rezervimeve dhe pagesave.
- **roles:** Rolet e përdoruesve (administrator, staf dhe klient).
- **activity_logs:** Regjistrimi i veprimeve të përdoruesve për qëllime auditimi.

### 7. Arkitektura e sistemit