# Sistemi për Menaxhimin e Agjencive Turistike

## Raporti 1 – Projekti

### Ideja e projektit

Ideja e këtij projekti është zhvillimi i një sistemi për menaxhimin e agjencive turistike, i cili mundëson menaxhimin e destinacioneve turistike, ofertave, rezervimeve dhe pagesave në një platformë të vetme. Qëllimi është të lehtësohet puna e stafit të agjencisë, të zvogëlohet përdorimi i proceseve manuale dhe të organizohen më mirë të dhënat dhe aktivitetet e përditshme.

### Përdoruesit kryesorë

- **Administratori:** Menaxhon llogaritë dhe rolet e përdoruesve, destinacionet turistike dhe ofertat. Gjithashtu, përcjell pagesat dhe gjeneron raporte financiare mujore.
- **Stafi i agjencisë:** Shton dhe përditëson informacionet për destinacionet turistike dhe ofertat, si dhe shikon dhe menaxhon rezervimet e klientëve sipas lejeve të caktuara nga sistemi.
- **Klientët:** Rezervojnë oferta dhe shohin historikun e tyre.

### Kërkesat funksionale

Sistemi duhet t'u mundësojë përdoruesve këto funksionalitete:

- Përdoruesi mund të kyçet në sistem përmes email-it dhe fjalëkalimit.
- Sistemi duhet të dallojë rolin e përdoruesit dhe t'i mundësojë administratorit, stafit dhe klientit qasje në funksionalitetet përkatëse.
- Administratori dhe stafi i autorizuar duhet të shtojnë dhe përditësojnë destinacionet turistike, duke përfshirë emrin, përshkrimin dhe lokacionin.
- Administratori dhe stafi i autorizuar mund të krijojnë dhe përditësojnë oferta turistike, duke përcaktuar destinacionin, periudhën e udhëtimit, çmimin për person dhe numrin e vendeve të disponueshme.
- Klienti mund të shohë ofertat aktive dhe informacionet përkatëse për secilën ofertë.
- Klienti i regjistruar mund të rezervojë një ofertë duke zgjedhur datën e nisjes dhe numrin e personave. Sistemi duhet të kontrollojë nëse ka vende të mjaftueshme.
- Klienti mund të shohë historikun e rezervimeve të veta dhe të kërkojë anulimin e tyre, ndërsa stafi i autorizuar duhet të mund të menaxhojë statusin e rezervimeve.
- Administratori ose stafi i autorizuar mund të regjistrojë dhe menaxhojë pagesat, duke përfshirë shumën, datën dhe statusin e pagesës.
- Administratori mund të gjenerojë raporte financiare mujore që paraqesin pagesat e regjistruara dhe shumën totale për periudhën e zgjedhur.

### Kërkesat jofunksionale

- Fjalëkalimet e përdoruesve duhet të ruhen në mënyrë të sigurt dhe jo si tekst i thjeshtë.
- Funksionalitetet private duhet të jenë të qasshme vetëm për përdoruesit e autentikuar dhe të autorizuar sipas rolit të tyre.
- Sistemi duhet të kontrollojë të dhënat e futura nga përdoruesi para se ato të ruhen në databazë.
- Ndërfaqja duhet të jetë e thjeshtë për t'u përdorur dhe responsive, në mënyrë që të përshtatet me kompjuterë, tableta dhe telefona.
- Informacionet për destinacionet, ofertat, rezervimet dhe pagesat duhet të jenë të organizuara dhe të lehta për t'u gjetur.
- Të dhënat duhet të ruhen në mënyrë të organizuar në databazën MySQL, duke ruajtur lidhjet e sakta ndërmjet tabelave.
- Sistemi duhet të parandalojë rezervimet kur numri i vendeve të disponueshme nuk është i mjaftueshëm dhe të shmangë gabimet gjatë regjistrimit të të dhënave.

### Dizajni teknik

Për zhvillimin e sistemit do të përdoret një arkitekturë që ndan ndërfaqen e përdoruesit, logjikën e aplikacionit dhe ruajtjen e të dhënave. Kjo strukturë do të mundësojë organizimin më të mirë të funksionaliteteve dhe komunikimin ndërmjet pjesëve të sistemit.

- **Frontend: HTML, CSS dhe JavaScript** – do të përdoren për krijimin e ndërfaqes së përdoruesit, dizajnin, navigimin dhe funksionalitetet interaktive të platformës. HTML do të përdoret për strukturën e faqeve, CSS për dizajnin dhe përshtatjen e tyre në pajisje të ndryshme, ndërsa JavaScript për ndërveprimet me përdoruesit.

- **Backend: PHP** – do të përdoret për logjikën e sistemit, autentifikimin e përdoruesve, përpunimin e të dhënave dhe komunikimin ndërmjet frontend-it dhe databazës. PHP do të mundësojë gjithashtu menaxhimin e destinacioneve, ofertave, rezervimeve dhe pagesave, sipas rolit të përdoruesit.

- **Databaza: MySQL** – do të përdoret për ruajtjen dhe menaxhimin e të dhënave të përdoruesve, roleve, destinacioneve turistike, ofertave, rezervimeve dhe pagesave. Tabelat do të lidhen përmes çelësave primarë (PK) dhe çelësave të huaj (FK), për të siguruar organizimin dhe lidhjen e saktë të të dhënave.

### Diagrami i arkitekturës së sistemit

- Sistemi do të ndërtohet me arkitekturë me tri shtresa, ku secila pjesë do të ketë përgjegjësitë e veta.
- **Frontend-i** do të mundësojë ndërveprimin e përdoruesve me platformën, **backend-i** do të përpunojë kërkesat dhe logjikën e sistemit, ndërsa **databaza** do të ruajë të dhënat.

**Rrjedha e sistemit:**
Përdoruesit (Administratori / Stafi / Klienti)
                    ↓
Frontend (HTML / CSS / JavaScript)
                    ↓ HTTP
Backend (PHP)
                    ↓
Database (MySQL)


### Modeli fillestar i databazes

1. Rolet
- role_id (PK)
- emri_rolit

2. Perdoruesit
- perdoruesi_id (PK)
- emri
- mbiemri
- email
- fjalkalimi_hash
- role_id (FK)

3. Destinacionet
- destinacioni_id (PK)
- d_emri
- pershkrimi
- lokacioni

4. Ofertat
- oferta_id (PK)
- destinacioni_id (FK)
- titulli
- pershkrimi
- data_fillimit
- data_mbarimit
- cmimi_per_person
- vendet_e_lira
- statusi

5. Rezervimet
- rezervimi_id (PK)
- perdoruesi_id (FK)
- oferta_id (FK)
- data_nisjes
- numri_personave
- shuma_totale
- statusi
- data_krijimit

6. Pagesat
- pagesa_id (PK)
- rezervimi_id (FK)
- shuma
- data_pageses
- statusi

### Relacionet kryesore

- Një Rol mund të ketë shumë përdorues  → 1:N
- Një Destinacion mund të ketë shumë oferta  → 1:N
- Një Përdorues (klient) mund të bëjë shumë rezervime → 1:N
- Një Ofertë mund të ketë shumë rezervime → 1:N
- Një Rezervim mund të ketë një ose më shumë pagesa → 1:N

### Plani për Raportin 2

Deri në Raportin 2 planifikohet të realizohet baza funksionale e Sistemit për Menaxhimin e Agjencive Turistike, me fokus në databazën, logjikën e backend-it dhe funksionalitetet kryesore.

- Krijimi i strukturës së projektit duke përdorur HTML, CSS, JavaScript dhe PHP.
- Krijimi dhe konfigurimi i databazës MySQL, duke përfshirë tabelat dhe relacionet ndërmjet tyre.
- Përcaktimi i çelësave primarë, çelësave të huaj dhe kufizimeve për ruajtjen e saktësisë së të dhënave.
- Zhvillimi i funksionalitetit për kyçjen e përdoruesve dhe kontrollin e qasjes sipas roleve.
- Implementimi i funksioneve për shtimin, shfaqjen, përditësimin dhe fshirjen e destinacioneve turistike dhe ofertave.
- Zhvillimi i funksionalitetit për krijimin dhe menaxhimin e rezervimeve, duke kontrolluar vendet e disponueshme.
- Implementimi fillestar i regjistrimit të pagesave dhe lidhjes së tyre me rezervimet.
- Krijimi dhe testimi i kërkesave të backend-it ose endpoint-eve për komunikimin me databazën.
- Testimi i funksionaliteteve me të dhëna testuese dhe trajtimi i gabimeve gjatë përpunimit të kërkesave.
- Përditësimi i repository-t në GitHub dhe dokumentimi i ndryshimeve, problemeve teknike dhe zgjidhjeve të tyre.