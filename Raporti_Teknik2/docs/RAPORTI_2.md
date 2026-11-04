# Raporti Teknik 2 — NEXORA

**Tema:** Sistemi i Menaxhimit të Agjencive Turistike  
**Teknologjitë:** HTML, CSS, JavaScript, PHP 8+, MySQL 8, PDO, phpMyAdmin  
**Fokusi:** databaza, backend-i dhe API; jo dizajni final i frontend-it.

## Çfarë është implementuar
- Skemë referuese me 6 tabela, PK/FK, indekse unike dhe constraints.
- Login me `password_verify`, sesione, logout dhe kontroll roli administrator.
- CRUD për destinacione, oferta, rezervime dhe pagesa.
- Validim server-side, prepared statements, CSRF, escaping HTML.
- API JSON me GET, POST, PATCH, DELETE; përgjigje dhe status codes.
- Dashboard me të dhëna reale nga databaza.

## Rrjedha e një rezervimi
1. Administratori hap formularin e rezervimit.
2. PHP kontrollon sesionin, rolin dhe token-in CSRF.
3. Validimi kontrollon përdoruesin, ofertën dhe numrin e personave.
4. `PDO::prepare()` ekzekuton `INSERT` në `rezervimet`.
5. MySQL kontrollon lidhjet FK dhe constraints.
6. PHP kthen mesazhin e suksesit dhe ridrejton te lista.

## Demonstrimi live (8–12 min)
1. Problemi dhe progresi nga Raporti 1 (1 min).
2. phpMyAdmin: 6 tabela, PK/FK, skema dhe të dhënat (2 min).
3. Kodi: PDO, login, sesione dhe validim (2 min).
4. Live demo: shto ofertë dhe rezervim, ndrysho statusin, shiko MySQL (3 min).
5. API: GET dhe një gabim 404/422; shpjego status codes (1–2 min).
6. Git history, problemet e hasura, pyetjet (1–2 min).

## Pyetje të mundshme
- Pse FK nga rezervimet te perdoruesit dhe ofertat? Sepse një përdorues/ofertë mund të lidhet me shumë rezervime.
- Si parandalohet SQL injection? Me PDO prepared statements.
- Si ruhet password-i? Me hash; kontrollohet me `password_verify`.
- Çfarë ndodh kur ID nuk ekziston? API kthen 404; formulari refuzon lidhjet e pavlefshme.
- Pse përdoret CSRF? Për të parandaluar kërkesat ndryshuese të paautorizuara nga një faqe tjetër.

## Probleme dhe zgjidhje (plotëso me punën tënde reale)
- **Problem i hasur:** ______________________
- **Si u zgjidh:** ___________________________
- **Commit / evidencë:** ____________________

**Kujdes:** Databaza origjinale e përdoruesit nuk është eksportuar në ZIP-in fillestar. `schema.sql` është skemë e re referuese dhe mund të mos përputhet me kolonat ekzistuese. Duhet verifikuar ose instaluar në databazë të veçantë para demonstrimit. Testimi live dhe Git history duhet të kryhen nga studenti; nuk pretendohet që janë kryer këtu.
