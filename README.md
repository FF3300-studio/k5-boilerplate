# Kirby 5 Boilerplate

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

Kirby richiede una licenza commerciale per la produzione. Il codice personalizzato contenuto in questo boilerplate può essere distribuito con la licenza del progetto (aggiornare questa sezione in base alle esigenze).
