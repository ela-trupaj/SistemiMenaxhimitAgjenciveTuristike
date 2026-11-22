<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8">
    <meta name="author" content="Ela Trupaj">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LUNÉA Voyages Travel Agency – zbuloni destinacione dhe oferta turistike.">

    <title>LUNÉA Voyages | Travel Agency</title>

    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- BOXICONS -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="style.css">

</head>

<body>

    <!-- HEADER -->
    <header class="site-header">
        <nav class="navbar container" aria-label="Navigimi kryesor">

            <a href="#ballina" class="brand" aria-label="LUNÉA Voyages - Ballina">
                <span class="brand-words">
                    <strong>LUNÉA</strong>
                    <small>VOYAGES</small>
                </span>
            </a>

            <button class="menu-toggle" type="button" aria-label="Hap menynë" aria-expanded="false" aria-controls="nav-links">
                <i class="bx bx-menu"></i>
            </button>

            <div class="nav-links" id="nav-links">
                <a class="active" href="#">Ballina</a>
                <a href="#">Destinacionet</a>
                <a href="#">Ofertat</a>
                <a href="#">Rreth Nesh</a>
                <a href="#">Kontakti</a>

                <div class="mobile-auth">
                    <a href="login.php">Kyçu</a>
                    <a href="register.php">Regjistrohu</a>
                </div>
            </div>

            <div class="auth-links">
                <a class="btn btn-login" href="login.php"> 
                    <i class="bx bx-user"></i> Kyçu 
                </a>
                <a class="btn btn-register" href="register.php"> Regjistrohu</a>
            </div>

        </nav>
    </header>

    <main>

        <!-- HERO SECTION -->
        <section class="hero" id="ballina">

            <div class="hero-shade"></div>

            <div class="container hero-content">
                <span class="hero-eyebrow">
                    <i class="bx bxs-plane-alt"></i>
                    Udhëtime në mbarë botën
                </span>

                <h1>Zbulo botën me <br> LUNÉA Voyages</h1>
                <p> Destinacione të veçanta, oferta të shkëlqyera dhe përvoja të paharrueshme udhëtimi. </p>

                <div class="hero-actions">
                    <a href="#ofertat" class="btn btn-primary"> Shiko Ofertat
                        <i class="bx bx-right-arrow-alt"></i>
                    </a>
                    <a href="#rreth-nesh" class="btn btn-outline">Rreth Nesh</a>
                </div>
            </div>

        </section>

        <!-- SEARCH SECTION -->
        <section class="search-area container" aria-label="Kërko oferta turistike">
            <form class="search-panel" id="search-form">

                <!-- DESTINACIONI -->
                <label class="search-field">
                    <i class="bx bx-map"></i>
                    <span>
                        <strong>Destinacioni</strong>

                        <select id="destination" aria-label="Destinacioni">
                            <option value="">Zgjidh destinacionin</option>
                            <option value="Santorini">Santorini</option>
                            <option value="Dubai">Dubai</option>
                            <option value="Romë">Romë</option>
                            <option value="Stamboll">Stamboll</option>
                            <option value="Paris">Paris</option>
                        </select>
                    </span>
                </label>

                <!-- DATA E NISJES -->
                <label class="search-field">
                    <i class="bx bx-calendar"></i>
                    <span>
                        <strong>Data e nisjes</strong>
                        <input id="departure" type="date" aria-label="Data e nisjes" required>
                    </span>
                </label>

                <!-- DATA E KTHIMIT -->
                <label class="search-field">
                    <i class="bx bx-calendar"></i>
                    <span>
                        <strong>Data e kthimit</strong>
                        <input id="return-date" type="date" aria-label="Data e kthimit" required >
                    </span>
                </label>

                <!-- UDHËTARËT -->
                <label class="search-field">
                    <i class="bx bx-group"></i>

                    <span>
                        <strong>Udhëtarët</strong>

                        <select id="travelers" aria-label="Numri i udhëtarëve">
                            <option value="1">1 person</option>
                            <option value="2">2 persona</option>
                            <option value="3">3 persona</option>
                            <option value="4">4 persona</option>
                            <option value="5">5 persona</option>
                            <option value="6">6 persona</option>
                        </select>
                    </span>

                </label>

                <button type="submit" class="btn search-btn">
                    <i class="bx bx-search"></i>
                    Kërko Oferta
                </button>
            </form>
            <p class="search-feedback" id="search-feedback" role="status" aria-live="polite"></p>
        </section>


        <!-- TRAVEL STORY SECTION -->
        <section class="travel-story" aria-labelledby="travel-story-title">

            <div class="container travel-story-grid">
                <div class="travel-story-content">

                    <span class="travel-story-eyebrow">LUNÉA VOYAGES · EXPLORE MORE</span>
                    <h2 id="travel-story-title">Udhëtime që kthehen në <em>kujtime.</em></h2>

                    <p>Çdo destinacion ka një histori për të treguar. Ne ju ndihmojmë të zbuloni vende të reja dhe të krijoni momente që mbeten gjatë.</p>

                    <a class="travel-story-link" href="#destinacionet">Eksploro destinacionet 
                        <i class="bx bx-right-arrow-alt" aria-hidden="true"></i>
                    </a>

                    <span class="travel-story-signature">Zbuloni botën përtej së zakonshmes.</span>
                </div>

                <div class="travel-story-visual">
                    <img src="assets/images/Home.png" alt="Pamje e një destinacioni bregdetar" loading="lazy">
                    <span class="travel-story-caption">ARTI I UDHËTIMIT · LUNÉA</span>
                </div>
            </div>
        </section>

        <!-- DESTINATIONS SECTION -->
        <section class="destinations-section" id="destinacionet">
            <div class="container">

                <div class="section-heading centered">
                    <h2>Destinacionet Popullore</h2>
                    <p>Zbuloni destinacionet më të kërkuara nga udhëtarët tanë.</p>
                </div>

                <div class="destinations-wrap">
                    <div class="destinations-track" id="destinations-track">
                        <!-- ROME -->
                        <a href="#ofertat" class="destination-card" data-destination="Romë" style="--card-image:url('assets/images/Rome.png')">
                            <span>
                                <i class="bx bxs-map"></i>
                                <b>Romë</b>
                                <small>Itali</small>
                            </span>
                        </a>

                        <!-- DUBAI -->
                        <a href="#ofertat" class="destination-card" data-destination="Dubai" style="--card-image:url('assets/images/Dubai .png')" >
                            <span>
                                <i class="bx bxs-map"></i>
                                <b>Dubai</b>
                                <small>Emiratet e Bashkuara</small>
                            </span>
                        </a>

                        <!-- SANTORINI -->
                        <a href="#ofertat" class="destination-card" data-destination="Santorini" style="--card-image:url('assets/images/Santorin.png')">
                            <span>
                                <i class="bx bxs-map"></i>
                                <b>Santorini</b>
                                <small>Greqi</small>
                            </span>
                        </a>

                        <!-- ISTANBUL -->
                        <a href="#ofertat" class="destination-card" data-destination="Stamboll" style="--card-image:url('assets/images/Instambul.png" >
                            <span>
                                <i class="bx bxs-map"></i>
                                <b>Stamboll</b>
                                <small>Turqi</small>
                            </span>
                        </a>

                        <!-- PARIS -->
                        <a href="#ofertat" class="destination-card" data-destination="Paris" style="--card-image:url('assets/images/Paris.png')" >
                            <span>
                                <i class="bx bxs-map"></i>
                                <b>Paris</b>
                                <small>Francë</small>
                            </span>
                        </a>

                    </div>

                    <button class="slider-next" id="slider-next" type="button" aria-label="Destinacioni tjetër">
                        <i class="bx bx-right-arrow-alt"></i>
                    </button>
                </div>
            </div>
        </section>

        <!-- OFFERS SECTION -->
        <section class="offers-section" id="ofertat">
            <div class="container">

                <div class="offers-heading">
                    <div class="section-heading">
                        <h2>Paketat më të Fundit</h2>
                        <p>Oferta speciale për udhëtimet tuaja të ardhshme.</p>
                    </div>

                    <button class="all-offers" id="all-offers" type="button">
                        Shiko të gjitha ofertat
                        <i class="bx bx-right-arrow-alt"></i>
                    </button>
                </div>

                <!-- SANTORINI -->
                <div class="offers-grid" id="offers-grid">

                    <article class="offer-card" data-destination="Santorini">
                        <img src="assets/images/Santorin.png" alt="Santorini, Greqi">

                        <div class="offer-info">
                            <span class="discount">-15%</span>
                            <h3>Santorini – Pushim Veror</h3>
                            <p>
                                <i class="bx bx-calendar"></i>
                                7 ditë / 6 netë
                            </p>

                            <p>
                                <i class="bx bx-group"></i>
                                Nga 1 person
                            </p>

                            <div class="offer-bottom">
                                <span>
                                    <strong>€699</strong> / person
                                </span>
                                <button class="offer-arrow" type="button" data-offer="Santorini – Pushim Veror" aria-label="Shiko Santorini">
                                    <i class="bx bx-right-arrow-alt"></i>
                                </button>
                            </div>
                        </div>
                    </article>


                    <!-- DUBAI -->
                    <article class="offer-card" data-destination="Dubai">
                        <img src="assets/images/Dubai .png" alt="Dubai" >
                        <div class="offer-info">
                            <span class="discount">-10%</span>
                            <h3>Dubai Experience</h3>

                            <p>
                                <i class="bx bx-calendar"></i>
                                6 ditë / 5 netë
                            </p>

                            <p>
                                <i class="bx bx-group"></i>
                                Nga 1 person
                            </p>

                            <div class="offer-bottom">
                                <span>
                                    <strong>€849</strong> / person
                                </span>

                                <button class="offer-arrow" type="button" data-offer="Dubai Experience" aria-label="Shiko Dubai">
                                    <i class="bx bx-right-arrow-alt"></i>
                                </button>
                            </div>
                        </div>
                    </article>

                    <!-- ROME -->
                    <article class="offer-card" data-destination="Romë">
                        <img src="assets/images/Rome.png" alt="Koloseu në Romë">

                        <div class="offer-info">
                            <span class="discount">-20%</span>
                            <h3>Romë Klasike</h3>

                            <p>
                                <i class="bx bx-calendar"></i>
                                4 ditë / 3 netë
                            </p>

                            <p>
                                <i class="bx bx-group"></i>
                                Nga 1 person
                            </p>

                            <div class="offer-bottom">
                                <span>
                                    <strong>€499</strong> / person
                                </span>

                                <button class="offer-arrow" type="button" data-offer="Romë Klasike" aria-label="Shiko Romën">
                                    <i class="bx bx-right-arrow-alt"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
                <p class="empty-state" id="empty-state" hidden> Nuk u gjet asnjë ofertë për destinacionin e zgjedhur.</p>
            </div>
        </section>

<section class="features-section" aria-labelledby="features-title">
    <div class="container">

        <div class="section-heading centered">
            <h2 id="features-title">Pse të udhëtoni me LUNÉA?</h2>
            <p> Përvoja të veçanta, siguri dhe përkushtim në çdo udhëtim tuajin.</p>
        </div>

        <div class="features">
            <div class="feature">
                <i class="bx bx-badge-check"></i>
                <span>
                    <strong>Oferta të sigurta</strong>
                    <small>Çmime të kontrolluara dhe transparente</small>
                </span>
            </div>

            <div class="feature">
                <i class="bx bx-paper-plane"></i>
                <span>
                    <strong>Destinacione në mbarë botën</strong>
                    <small>Evropë, Azi, Afrikë dhe më shumë</small>
                </span>
            </div>

            <div class="feature">
                <i class="bx bx-support"></i>
                <span>
                    <strong>Mbështetje 24/7</strong>
                    <small>Jemi gjithmonë pranë jush</small>
                </span>
            </div>

            <div class="feature">
                <i class="bx bx-shield-quarter"></i>
                <span>
                    <strong>Rezervim i lehtë</strong>
                    <small>I shpejtë, i sigurt dhe online</small>
                </span>
            </div>

        </div>
    </div>
</section>

    </main>

<!-- FOOTER -->
<footer class="footer" id="kontakti">
    <div class="footer-container">

        <div class="footer-about">
            <h3>LUNÉA Voyages</h3>
            <p> Çdo udhëtim fillon me një ëndërr. Zbuloni destinacione të mrekullueshme dhe krijoni kujtime që zgjasin përgjithmonë. </p>

            <div class="footer-social">
                <a href="#" aria-label="Facebook">
                    <i class="bx bxl-facebook"></i>
                </a>

                <a href="#" aria-label="Instagram">
                    <i class="bx bxl-instagram"></i>
                </a>

                <a href="#" aria-label="TikTok">
                    <i class="bx bxl-tiktok"></i>
                </a>
            </div>
        </div>

        <div class="footer-links">
            <h4>Linqe të Shpejta</h4>

            <a href="#">Ballina</a>
            <a href="#">Destinacionet</a>
            <a href="#">Ofertat</a>
            <a href="#">Rreth Nesh</a>
            <a href="#">Kontakti</a>
        </div>

        <div class="footer-contact">
            <h4>Na Kontaktoni</h4>
            <p>
                <i class="bx bxs-map"></i>
                Prizren, Kosovë
            </p>

            <p>
                <i class="bx bxs-phone"></i>
                +383 44 123 123
            </p>

            <p>
                <i class="bx bxs-envelope"></i>
                info@luneavoyages.com
            </p>

        </div>

        <div class="footer-reservation">

            <h4>Planifikoni Udhëtimin</h4>
            <p> Destinacioni juaj i ëndrrave ju pret.Zgjidhni aventurën e radhës dhe udhëtoni me LUNÉA Voyages.</p>
            <a href="#ofertat" class="footer-btn">
                SHIKO OFERTAT
                <i class="bx bx-right-arrow-alt"></i>
            </a>
        </div>
    </div>

    <div class="footer-bottom">
        <p>
            © <span id="year"></span> LUNÉA Voyages.
            Të gjitha të drejtat e rezervuara.
        </p>
    </div>
</footer>


    <!-- OFFER DIALOG -->
    <dialog id="offer-dialog">

        <button class="dialog-close" id="dialog-close" type="button" aria-label="Mbyll" > 
            <i class="bx bx-x" aria-hidden="true"></i>
        </button>

        <h2>Detajet e ofertës</h2>
        <p id="dialog-text"></p>

        <button class="btn btn-primary" id="dialog-ok" type="button"> Rezervo </button>
    </dialog>

    <!-- JAVASCRIPT -->
    <script src="script.js" defer></script>
</body>
</html>
