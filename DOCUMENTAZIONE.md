# Documentazione operativa del boilerplate Kirby 5

## Introduzione: uno strumento non deterministico, parametrico e "liquido"
Questo boilerplate nasce per costruire siti editoriali basati su [Kirby 5](https://getkirby.com/) mantenendo una filosofia progettuale "liquida":
- **Non deterministico** perché le pagine vengono composte da layout e blocchi dinamici, governati da hook e blueprint che propagano parametri dal parent al child, lasciando al redattore libertà nell'orchestrare i contenuti.
- **Parametrico** grazie alle numerose opzioni configurabili dal Panel (filtri, alias, colori, TTL di cache), ai token SCSS e alle variabili Vite che consentono di ri-temare velocemente il frontend.
- **Liquido** in quanto ogni componente (snippet, blocco, modello) può essere riutilizzato in contesti diversi, adattandosi ai dati disponibili (CSV locali, Google Sheet, collezioni Kirby) e restituendo markup semantico coerente.

La struttura modulare sfrutta Kirby come orchestratore dei dati e Vite come toolchain front-end moderna, consentendo di comporre rapidamente esperienze editoriali ricche senza rinunciare al controllo di basso livello.

Ogni pagina può essere trattata come una **collection**: se il redattore abilita i relativi flag nel Panel, la pagina eredita viste dedicate (lista, mappa, calendario), categorie e altri parametri che vengono propagati ai figli. In questo modo le sottopagine reagiscono allo stato del parent (ad esempio attivando filtri se le categorie sono abilitate o mostrando la mappa se la vista geografica è selezionata). Tutte le pagine continuano a usare il template `default`, ma la combinazione dinamica di parametri e layout permette di specializzarle progressivamente man mano che il data entry prende forma: è il contenuto stesso a plasmare la struttura del sito, non viceversa.

## Soluzioni architetturali principali
### Hook di sincronizzazione
Gli hook definiti in `site/config/hooks.php` propagano automaticamente ai figli i flag ereditati (`collection_options`, `collection_categories_manager_toggle`) quando una pagina viene creata, pubblicata o aggiornata. Questo evita divergenze tra blueprint e contenuti reali, mantenendo coerente la logica condizionale del Panel.【F:site/config/hooks.php†L1-L69】

### Layout a blocchi
Il template principale (`site/templates/default.php`) delega la composizione dei contenuti allo snippet `layouts`, che trasforma i campi Layout del Panel in righe Bootstrap-like, supportando sticky block, ancore automatiche e ID personalizzati. Lo snippet calcola inoltre i dati del form associato per gestire scadenze e disponibilità, rendendo la pagina modulare e reattiva alle variabili di contesto.【F:site/templates/default.php†L1-L26】【F:site/snippets/layouts.php†L1-L128】

### Modelli specializzati per l'import di dati
Due model estendono il comportamento di base di Kirby per gestire sorgenti esterne:
- `SpreadsheetPage` implementa caching con conditional GET, lock anti-stampede, mapping alias dinamici e generatori di filtri per trasformare CSV remoti in collezioni navigabili dal frontend e dall'API JSON/CSV.【F:site/models/spreadsheet.php†L1-L146】
- `SediPage` importa righe da Google Spreadsheet o da file CSV caricati nel Panel, normalizza header, gestisce TTL differenti fra Panel e frontend e crea pagine figlie virtuali con UUID per popolare mappe e liste di sedi.【F:site/models/sedi.php†L1-L144】

Questi model mantengono il progetto "liquido" verso le fonti dati, permettendo di cambiare sorgente senza modificare i template.

### Toolchain front-end
Vite (configurata in `vite.config.js`) compila gli asset e integra il plugin di live reload per blueprint, snippet e asset. Gli script (`assets/src/js/scripts.js`) includono interazioni con jQuery, Swiper e helper per lazyload, mantenendo il frontend parametrico e aggiornato senza rebuild manuali.【F:vite.config.js†L1-L91】【F:assets/src/js/scripts.js†L1-L170】

## Componenti custom
### Snippet e blocchi
La cartella `site/snippets/` raccoglie componenti PHP riutilizzabili: header, menu, mappe, card, paginator e snippet per interfacce di collezione.【F:README.md†L43-L55】 La sottocartella `blocks/` contiene i blocchi custom per il field Blocks (gallery, image, titles, video), mentre i tre snippet `block-slide-*` implementano uno slider modulare per immagini, testi e video.【F:site/snippets/block-slide-image.php†L1-L42】

Lo snippet `collection-*` offre viste alternative (griglia, calendario, mappa) sulle collezioni importate, mentre `form-request-counter*.php` calcola e visualizza in modo parametrico i posti disponibili per i form legati alle pagine.【F:site/snippets/form-request-counter.php†L1-L40】

## Campi dinamici e logiche collegate
- **Deadline editoriale** – Il field data `deadline` è disponibile solo quando la pagina non è una collection e il parent è configurato con vista calendario, così da evitare ridondanze sul Panel.【F:site/blueprints/fields/deadline.yml†L1-L5】 Il valore è usato dal layout fluido per nascondere automaticamente le righe contrassegnate come "Scade" se la data è passata o non ci sono più posti, mantenendo il markup pulito anche senza intervento manuale.【F:site/blueprints/fields/layout_settings.yml†L20-L41】【F:site/snippets/layouts.php†L1-L125】
- **Badge e notifiche contestuali** – Le card delle collection leggono la deadline per generare badge dinamici ("Iscrizioni aperte", "Manca poco", "Iscrizioni chiuse") e per abilitare il contatore dei posti soltanto quando la data è ancora valida, evitando promozioni di eventi già scaduti.【F:site/snippets/card-info.php†L1-L155】
- **Numero massimo e disponibilità** – Il campo numerico `num_max` espone nel Panel il tetto di iscrizioni: la closure `formData` calcola risposte lette, percentuale di completamento e posti residui a partire dalle pagine `formrequest`, rendendo questi dati disponibili a snippet e viste JSON/CSV.【F:site/blueprints/fields/num_max.yml†L1-L5】【F:site/controllers/default.php†L87-L173】 Lo snippet `form-request-counter` traduce tali informazioni in micro visualizzazioni (barra di avanzamento, tacche, avviso posti esauriti) riutilizzabili nei layout e nelle schede del calendario.【F:site/snippets/form-request-counter.php†L1-L40】

### Plugin inclusi
La cartella `site/plugins/` integra plugin first-party e custom (block factory, suite di blocchi form, better search, locator) che ampliano il Panel con blocchi avanzati, gestione mappe e utilità di redazione. L'insieme consente di definire blocchi editoriali complessi senza sviluppare logica ad hoc per ogni progetto.【F:README.md†L7-L14】

## Struttura SCSS
Gli asset Sass risiedono in `assets/src/sass/`:
- `style.scss` funge da entry point e importa il tema.
- La cartella `theme/` è organizzata in sottocartelle rispecchiando i componenti PHP: `settings/` per token e override Bootstrap, `base/` per reset e tipografia, `layout/` per griglie e container, `components/` per partial specifici (header, footer, mappe, slider, filtri), `utilities/` per helper trasversali. I partial sono indicizzati tramite file `_index.scss` per mantenere la parità con snippet e template PHP.【F:assets/src/sass/theme/_index.scss†L1-L23】【F:assets/src/sass/theme/components/_index.scss†L1-L18】

Questa corrispondenza facilita l'evoluzione congiunta tra markup e stile: ogni snippet principale ha il proprio partial SCSS omonimo, mantenendo l'architettura leggibile.

## Istanziazione del progetto
Per avviare un nuovo progetto a partire da questo boilerplate:
1. Clonare il repository e installare le dipendenze PHP con `composer install` per recuperare Kirby e i plugin dichiarati in `composer.json`.
2. Installare le dipendenze front-end con `npm install`.
3. Avviare l'ambiente di sviluppo con `npm run dev`, che esegue Vite e il server PHP integrato puntando al router Kirby.
4. Accedere a `http://127.0.0.1:8000` per il frontend e a `/panel` per il backoffice (creare l'utente admin al primo accesso).【F:README.md†L21-L43】

Per la build di produzione usare `npm run build` e distribuire `kirby/`, `site/`, `assets/`, `index.php` e `vendor/` secondo la pipeline descritta nel README.【F:README.md†L78-L91】

## Personalizzare i settings base del frontend
Le impostazioni chiave di tipografia e colore sono centralizzate in `assets/src/sass/theme/settings/_tokens.scss`. Qui si definiscono:
- Peso e font-face custom (famiglia `freak`, varianti di `Instrument Sans`).
- Tavolozza cromatica del tema (`$color-theme`, `$color-theme-bis`, `$color-hover`, ecc.).
- Scala tipografica per viewports differenti e spaziature base.
- Breakpoint della griglia responsive.【F:assets/src/sass/theme/settings/_tokens.scss†L1-L86】

Per cambiare font o colori è sufficiente modificare questi token e ricompilare con Vite: tutti i componenti che referenziano le variabili erediteranno automaticamente le nuove scelte.

## Parametrizzare ulteriormente
- Le opzioni globali (lingua, cache, asset) si impostano in `site/config/options.php` e nei file caricati da `config.php`.【F:site/config/options.php†L1-L23】
- I blueprint in `site/blueprints/` definiscono campi e toggles che alimentano i model; aggiornarli permette di esporre nuovi parametri nel Panel.【F:README.md†L45-L55】
- Gli snippet possono ricevere `props` aggiuntive dai template per comportamenti contestuali (es. `layouts` accetta `class`, `custom_style`, `formData`).【F:site/snippets/layouts.php†L1-L118】

Seguendo questo approccio modulare, ogni nuovo progetto può essere modellato rapidamente adeguando parametri e dati senza stravolgere la struttura portante.
