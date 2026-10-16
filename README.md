# SistemiMenaxhimitAgjenciveTuristike

Raporti 1 – Projekti: Sistem për Menaxhimin e Agjencive Turistike

1.Ideja e projektit
Ideja e këtij projekti është zhvillimi i një sistemi për menaxhimin e agjencive turistike, i cili mundëson menaxhimin e destinacioneve turistike, ofertave, rezervimeve dhe pagesave në një platformë të vetme. Qëllimi është të lehtësohet puna e stafit të agjencisë, të zvogëlohet përdorimi i proceseve manuale dhe të organizohen më mirë të dhënat dhe aktivitetet e përditshme.

2.Përdoruesit kryesorë:
•	Stafi i agjencisë (menaxhon destinacionet, ofertat, rezervimet).
•	Administratorët (menaxhojnë pagesat, raportet financiare).
•	Klientët (rezervojnë oferta dhe shohin historikun e tyre).


3.Kërkesat Funksionale	

* Përdoruesi i autentikuar (stafi) mund të shtojë destinacione turistike me emër, përshkrim, çmim dhe datë.	
* Administratori mund të krijojë oferta turistike duke zgjedhur destinacionin, periudhën dhe çmimin.
* Klienti i regjistruar mund të bëjë rezervim duke zgjedhur datën e nisjes dhe numrin e personave.
* Sistemi mundëson anulimin e rezervimit nga klienti ose stafi.	
* Administratori menaxhon pagesat dhe gjeneron raporte financiare mujore.	
* Klienti mund të shohë historikun e rezervimeve të tij.
* Sistemi dërgon njoftime automatike me email për konfirmimin e rezervimit dhe pagesës.

4.Kërkesat Jofunksionale

* Password-et ruhen vetëm në formë të hash-uar dhe endpoint-et private kërkojnë autentikim.	
* Interface-i duhet të jetë i thjeshtë dhe i përdorshëm edhe në ekran mobil.	
* Sistemi duhet të përpunojë rezervime pa vonesa të dukshme (p.sh. të paktën 50 rezervime njëkohësisht).
* Platforma duhet të jetë e aksesueshme 99% të kohës gjatë vitit.
* Sistemi duhet të mbështesë rritjen e numrit të përdoruesve pa ndryshime të mëdha në arkitekturë.
* Të dhënat personale të klientëve ruhen dhe përpunohen sipas politikave të mbrojtjes së të dhënave.

5.Dizajni teknik

Frontend: 
    HTML, CSS, JavaScript; dizajn responsiv për përdorim në desktop dhe mobile
Backend: 
    PHP për autentifikim, menaxhimi i roleve, destinacione, oferta, rezervime, pagesa dhe njoftimeve automatike
Database: 
    MySQL për ruajtjen e entiteteve dhe relacionet ndërmjet tyre.



users	
llogaritë e përdoruesve (email, password hash, roli).

destinations	
destinacionet turistike (emër, përshkrim, çmim, datë).

offers	
ofertat e lidhura me destinacionet (periudhë, çmim, detaje).

reservations	
rezervimet e klientëve (datë nisje, nr. personave, status).

payments	
pagesat e rezervimeve (shuma, data, status).

notifications	
njoftimet automatike për konfirmime rezervimi dhe pagesash.

roles	
rolet e përdoruesve (admin, staf, klient).

activity_logs	
regjistrimi i veprimeve të përdoruesve (auditim).


7.Arkitektura e sistemit



