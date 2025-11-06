
# Documentazione analitica

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

Lo snippet `collection-*` offre viste alternative (griglia, calendario, mappa) sulle collezioni importate, mentre `form-request-counter*.php` calcola e visualizza in modo parametrico i posti disponibili per i form legati alle pagine.【F:site/snippets/form-request-counter.php†L1-L120】

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

# Documentazione sintetica

Questo repository raccoglie una installazione completa di [Kirby 5](https://getkirby.com/) predisposta come base di partenza per progetti editoriali multilingua. Il backend resta fedele alle convenzioni Kirby (blueprint, controller, snippet, model) mentre il frontend utilizza Vite per compilare Sass e JavaScript moderni. Tutti i file di contenuto generati in locale (cartelle `content/`, `media/` ecc.) sono esclusi dal controllo versione così da poter partire da un ambiente pulito.

## Stack tecnologico

- **PHP 8.2+** con Composer per gestire Kirby CMS (`getkirby/cms`) e il plugin [sylvainjule/locator](https://github.com/sylvainjule/kirby-locator).
- **Kirby CMS 5.1** installato nella cartella `kirby/` tramite composer-installer.
- **Plugin Kirby inclusi** in `site/plugins/`:
  - componenti per blocchi (`block-factory`, `kirby-form-block-suite`),
  - utilità di redazione (`k3-whenquery`, `kirby-bettersearch`, `kirby-code-editor`, `kirby3-video-master`, `kirby3-cookie-banner`, `cleantext`, `utility-kirby`),
  - il plugin `locator` per gestire coordinate geografiche e marker.
- **Node 18+** con Vite 6, Sass e Bootstrap 5.3 per la compilazione degli asset.
- **Vite plugin live-reload** per riavviare automaticamente il browser quando cambiano blueprint, snippet e asset sorgente.

## Requisiti

| Tool        | Versione suggerita | Note |
|-------------|-------------------|------|
| PHP         | >= 8.2            | Estensioni consigliate: `intl`, `gd` |
| Composer    | 2.x               | installa Kirby core e plugin |
| Node.js     | >= 18             | utilizzare `nvm` per allineare il team |
| npm         | >= 9              | gestisce dipendenze Vite |

## Avvio rapido

```bash
composer install          # scarica Kirby e i plugin PHP
npm install               # installa le dipendenze front-end
npm run dev               # avvia Vite + server PHP integrato
```

- Vite avvia automaticamente un server PHP integrato su `127.0.0.1:8000` (router Kirby) e serve asset con hot reload su `127.0.0.1:3004`.
- Per un build pronto alla pubblicazione eseguire `npm run build`: gli asset vengono compilati in `assets/build/` senza cancellare file esistenti.

### Script npm disponibili

| Comando        | Cosa fa |
|----------------|---------|
| `npm run dev`  | Esegue Vite in modalità sviluppo e avvia `php -S 127.0.0.1:8000 kirby/router.php`. |
| `npm run build`| Compila Sass/JS da `assets/src/` verso `assets/build/` utilizzando l'output configurato in `vite.config.js`. |

## Struttura del progetto

| Percorso                | Descrizione |
|-------------------------|-------------|
| `index.php`             | Bootstrap di Kirby, carica `kirby/bootstrap.php` e serve il sito. |
| `assets/src/`           | Sorgenti Sass e JavaScript. `sass/style.scss` e `js/scripts.js` sono i principali entry-point. |
| `assets/build/`         | Output compilato da Vite (ignorato in Git). |
| `site/config/`          | Configurazione modulare suddivisa in `options.php`, `panel.php`, `hooks.php`, `routes.php` uniti da `config.php`. |
| `site/controllers/`     | Controller PHP; `default.php` gestisce collezioni, categorie, mappe e statistiche dei form. |
| `site/models/`          | Model personalizzati (`DefaultPage`, `SpreadsheetPage`) con helper per categorie e import CSV. |
| `site/templates/`       | Template Kirby per HTML/JSON/CSV (default, search, spreadsheet...). |
| `site/snippets/`        | Snippet riutilizzabili per header, banner, layout modulari, liste correlate, sitemap ecc. |
| `site/blueprints/`      | Blueprint per pagine, blocchi, file e opzioni del sito; definiscono campi, tab e logiche Panel. |
| `kirby/`                | Core Kirby gestito da Composer. |
| `vendor/`               | Dipendenze PHP installate da Composer (ignorate in Git per evitare file binari). |

## Backend Kirby

### Configurazione modulare

`site/config/config.php` carica in cascata `options.php`, `panel.php`, `hooks.php` e `routes.php`, poi applica eventuali override locali da `_local.php` (file ignorato da Git). Le opzioni principali includono lingua italiana di default, `debug` abilitato in sviluppo, cache attiva e generazione di thumbnail WebP tramite il driver GD con preset responsive.

### Hook di sincronizzazione

Gli hook definiti in `site/config/hooks.php` propagano automaticamente impostazioni dalla pagina genitore alle pagine figlie:
- **page.create:after** copia `collection_options` e flag relativi alle categorie appena viene creata una nuova pagina.
- **page.changeStatus:after** e **page.update:after** sincronizzano i campi derivati su tutti i figli quando il parent cambia stato o contenuto.

Questo evita discrepanze nelle condizioni `when` dei blueprint Panel e garantisce consistenza delle categorie.

### Controller principali

`site/controllers/default.php` fornisce al template pre-elaborazioni fondamentali:
- utility per filtrare le collezioni per categoria (logica `and`/`or`) e distinguere eventi futuri/passati in base all'ultimo appuntamento valido;
- generazione dell'array `locations_array` con coordinate e marker (default o specifici per categoria) per popolare mappe Leaflet/Mapbox;
- helper `formData()` che calcola iscritti, disponibilità e percentuale di completamento partendo da pagine `formrequest` collegate;
- calcolo di gruppi e categorie effettivamente utilizzati per alimentare filtri e interfacce Panel.

Il controller restituisce al template tutte le collezioni filtrate, le coordinate di default della mappa (zoom/centro) e un contatore placeholder `filter_counter` usato lato front-end.

### Model personalizzati

- `DefaultPage` garantisce che `categoriesOptions()` restituisca sempre una `Structure` anche se il parent non ha dati, evitando errori nelle condizioni Panel e nei template.
- `SpreadsheetPage` gestisce l'importazione e la cache di CSV remoti: implementa conditional GET, lock anti-stampede, mapping alias dei campi, filtri Panel, normalizzazione dei valori e servizi di ricerca per i template `spreadsheet.php` e `spreadsheet-item.php`.

### Template e snippet

- `site/templates/default.php` combina snippet (`header`, `menu`, `layouts`, `check_collection`, `page_related_list`, `footer`) per comporre la pagina principale.
- `default.json.php` e `default.csv.php` esportano i dati di pagina e delle figlie con formattazione data italiana, tagli orari e serializzazione YAML -> JSON/CSV.
- Template aggiuntivi (`search.php`, `spreadsheet.php`) consumano i metodi dei model per generare API e viste tabellari.

## Frontend

- `assets/src/sass/style.scss` raccoglie i partial SCSS del progetto ed è pronto ad importare Bootstrap o ulteriori variabili (commenti già predisposti in `vite.config.js`).
- `assets/src/js/scripts.js` contiene gli script con jQuery: toggle della navigazione mobile, slider Swiper per liste di card, hook per collapse, e scaffolding (commentato) per mixitup/ lazyload.
- `vite.config.js` imposta `assets/src` come root, compila CSS/JS in sottocartelle dedicate, mantiene gli asset esistenti e usa `vite-plugin-live-reload` per osservare blueprint/snippet (`site/`) e asset durante lo sviluppo.

Per utilizzare Swiper e LazyLoad in produzione ricordarsi di includere le librerie nel markup (`assets/js/` contiene script third-party pronti all'uso).

## Deployment

1. Eseguire `composer install --no-dev` e `npm run build` in ambiente di build.
2. Caricare via FTP/CI le cartelle `kirby/`, `site/`, `assets/`, il file `index.php` e tutta la cartella `vendor/` (se non si usa Composer sul server).
3. Copiare la cartella `content/` prodotta dal Panel e la cartella `media/` generata da Kirby dal proprio ambiente di staging/produzione.
4. Configurare eventuali variabili sensibili in `site/config/_local.php` o in file environment esterni.

Per invalidare la cache di `SpreadsheetPage` basta aggiungere il parametro `?refresh=1` all'URL desiderato; il modello ricostruirà i dati forzando il refetch del CSV.

## Buone pratiche

- Non committare file generati dal Panel (`content/`, `media/`, log, cache). Sono tutti esclusi tramite `.gitignore`.
- Tenere aggiornata la documentazione dei blueprint quando si aggiungono nuovi campi o tab.
- Eseguire `composer validate` e `npm run build` prima di aprire una pull request per intercettare errori sintattici.
- Usare branch feature e PR descrittive; il README può fungere da entry point per nuovi contributori.

## Licenza

Kirby richiede una licenza commerciale per la produzione.
