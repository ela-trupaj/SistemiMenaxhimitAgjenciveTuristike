const menuButton = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');

// Hapja dhe mbyllja e menusë
menuButton.addEventListener('click', () => {
    const open = navLinks.classList.toggle('open');

    menuButton.setAttribute('aria-expanded', String(open));
    menuButton.setAttribute(
        'aria-label',
        open ? 'Mbyll menynë' : 'Hap menynë'
    );

    menuButton.querySelector('i').className =
        open ? 'bx bx-x' : 'bx bx-menu';
});

// Mbyllja e menusë pas klikimit
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
        navLinks.classList.remove('open');

        menuButton.setAttribute('aria-expanded', 'false');
        menuButton.querySelector('i').className = 'bx bx-menu';
    });
});

// Datat e udhëtimit
const departure = document.getElementById('departure');
const returnDate = document.getElementById('return-date');

const localDate = date => {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
};

const today = localDate(new Date());

departure.min = today;
returnDate.min = today;

departure.addEventListener('change', () => {
    returnDate.min = departure.value || today;

    if (returnDate.value && returnDate.value < departure.value) {
        returnDate.value = '';
    }
});

// Filtrimi i ofertave
const cards = [...document.querySelectorAll('.offer-card')];
const feedback = document.getElementById('search-feedback');
const emptyState = document.getElementById('empty-state');

function filterOffers(destination) {
    let visible = 0;

    cards.forEach(card => {
        const match =
            !destination || card.dataset.destination === destination;

        card.hidden = !match;

        if (match) {
            visible++;
        }
    });

    emptyState.hidden = visible > 0;

    document.getElementById('ofertat').scrollIntoView({
        behavior: 'smooth'
    });

    return visible;
}

// Kërkimi i ofertave
document.getElementById('search-form').addEventListener('submit', event => {
    event.preventDefault();

    feedback.textContent = '';

    if (!departure.value || !returnDate.value) {
        feedback.textContent =
            'Ju lutemi zgjidhni datën e nisjes dhe të kthimit.';
        return;
    }

    if (departure.value < today || returnDate.value < departure.value) {
        feedback.textContent =
            'Data e kthimit duhet të jetë e njëjtë ose pas datës së nisjes.';
        return;
    }

    const destination = document.getElementById('destination').value;
    const visible = filterOffers(destination);

    feedback.style.color = visible ? '#0c4a9b' : '#b43b3b';

    feedback.textContent = visible
    ? `U gjetën ${visible} oferta për ${document.getElementById('travelers').value} udhëtar(ë).`
    : 'Nuk ka oferta të disponueshme për këtë destinacion.';
});

// Zgjedhja e destinacionit
document.querySelectorAll('.destination-card').forEach(card => {
    card.addEventListener('click', () => {
        document.getElementById('destination').value =
            card.dataset.destination;

        feedback.textContent = '';

        filterOffers(card.dataset.destination);
    });
});

// Shfaqja e të gjitha ofertave
document.getElementById('all-offers').addEventListener('click', () => {
    document.getElementById('destination').value = '';
    feedback.textContent = '';

    filterOffers('');
});

// Slider i destinacioneve
document.getElementById('slider-next').addEventListener('click', () => {
    const track = document.getElementById('destinations-track');

    if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 5) {
        track.scrollTo({
            left: 0,
            behavior: 'smooth'
        });
    } else {
        track.scrollBy({
            left: 240,
            behavior: 'smooth'
        });
    }
});

// Dritarja e detajeve të ofertës
const dialog = document.getElementById('offer-dialog');

document.querySelectorAll('[data-offer]').forEach(button => {
    button.addEventListener('click', () => {
        document.getElementById('dialog-text').textContent =
            `Oferta: ${button.dataset.offer}`;

        dialog.showModal();
    });
});

// Mbyllja e dritares
document.getElementById('dialog-close').addEventListener('click', () => {
    dialog.close();
});

document.getElementById('dialog-ok').addEventListener('click', () => {
    dialog.close();
});

// Viti automatik në footer
const footerYear = document.getElementById("year");

if (footerYear) {
    footerYear.textContent = new Date().getFullYear();
}

// PAGE LOADING & OFFLINE STATE
const loader = document.getElementById('page-loader');
const loaderMessage = document.getElementById('loader-message');
const spinner = document.querySelector('.loader-spinner');
const offlineIcon = document.getElementById('offline-icon');

function updateConnection() {
    if (!navigator.onLine) {
        loader.classList.remove('hidden');

        spinner.style.display = 'none';
        offlineIcon.hidden = false;

        loaderMessage.textContent =
            'Nuk ka lidhje me internetin.';
    } else {
        spinner.style.display = 'block';
        offlineIcon.hidden = true;

        loaderMessage.textContent = 'Duke u ngarkuar...';

        if (document.readyState === 'complete') {
            loader.classList.add('hidden');
        }
    }
}

window.addEventListener('load', updateConnection);
window.addEventListener('online', updateConnection);
window.addEventListener('offline', updateConnection);

updateConnection();