panel.plugin("cookbook/block-factory", {
  blocks: {
    map: {
      computed: {
        collection() {
          return this.field("collection");
        },
      },
      template: 
      ` 
        <div class="k-block-type-map">
          MAP
        </div>
      `
    },
    cta: {
      computed: {
        // Access the fields from the blueprint
        cta() {
          return this.field("cta").toStructure();
        }
      },
      template: `
        <div class="block-cta">
          <div v-for="(cta, index) in cta" :key="index" class="block-cta__item">
            <a :href="cta.esterno ? cta.url : cta.page.url" 
               :target="cta.target ? '_blank' : '_self'"
               v-html="cta.anteprima">
            </a>
          </div>
        </div>
      `,
      methods: {
        // Optional: methods for additional processing
        fetchPageData() {
          // No additional processing required for this block as fields are directly used in template
        }
      }
    },
    cta_w_auto: {
      computed: {
        // Access the fields from the blueprint
        cta() {
          return this.field("cta").toStructure();
        }
      },
      template: `
        <div class="block-cta">
          <div v-for="(cta, index) in cta" :key="index" class="block-cta__item">
            <a :href="cta.esterno ? cta.url : cta.page.url" 
               :target="cta.target ? '_blank' : '_self'"
               v-html="cta.anteprima">
            </a>
          </div>
        </div>
      `,
      methods: {
        // Optional: methods for additional processing
        fetchPageData() {
          // No additional processing required for this block as fields are directly used in template
        }
      }
    },
    cta_w_100: {
      computed: {
        // Access the fields from the blueprint
        cta() {
          return this.field("cta").toStructure();
        }
      },
      template: `
        <div class="block-cta">
          <div v-for="(cta, index) in cta" :key="index" class="block-cta__item">
            <a :href="cta.esterno ? cta.url : cta.page.url" 
               :target="cta.target ? '_blank' : '_self'"
               v-html="cta.anteprima">
            </a>
          </div>
        </div>
      `,
      methods: {
        // Optional: methods for additional processing
        fetchPageData() {
          // No additional processing required for this block as fields are directly used in template
        }
      }
    },
    people: {
    },
    cards: {
      computed: {
        // Access the fields from the blueprint
        title() {
          return this.field("title");
        },
        cards() {
          return this.field("pass");
        },
      },
      template: `
        <h1>Pass</h1>
        <div class="k-block-type-pass">
          <h2>{{ title }}</h2>
          <ul>
            <li v-for="page in pass" :key="page.id">
              <a :href="page.url">{{ page.title }}</a>
              <p>{{ page.yourField }}</p> <!-- Replace 'yourField' with actual field name -->
            </li>
          </ul>
        </div>
      `,
      methods: {
        // Ensure you handle how to fetch and process pages correctly
        fetchPageData() {
          // Assuming 'pass' field contains page references; adjust as needed
          return this.pass.map(page => {
            return {
              id: page.id,
              url: page.url,
              title: page.title,
              yourField: page.yourField // Replace with the actual field name you need
            };
          });
        }
      }
    },
    grid: {
      computed: {
        // Access the fields from the blueprint
        title() {
          return this.field("title");
        },
        grid() {
          return this.field("grid");
        },
      },
      template: `
        <h1>Grid</h1>
        <div class="k-block-type-grid">
          <h2>{{ title }}</h2>
          <ul>
            <li v-for="page in grid" :key="page.id">
              <a :href="page.url">{{ page.title }}</a>
              <p>{{ page.yourField }}</p> <!-- Replace 'yourField' with the actual field name you need -->
            </li>
          </ul>
        </div>
      `,
      methods: {
        // Ensure you handle how to fetch and process pages correctly
        fetchPageData() {
          // Assuming 'luoghi' field contains page references; adjust as needed
          return this.luoghi.map(page => {
            return {
              id: page.id,
              url: page.url,
              title: page.title,
              yourField: page.yourField // Replace with the actual field name you need
            };
          });
        }
      }
    },
    collection_manager: {
      computed: {
        // Access the fields from the blueprint
        title() {
          return this.field("title");
        },
        collection_manager() {
          return this.field("collection_manager");
        },
      },
      template: `
        <h1>Collection_manager</h1>
        <div class="k-block-type-collection_manager">
          <h2>{{ title }}</h2>
          <ul>
            <li v-for="page in collection_manager" :key="page.id">
              <a :href="page.url">{{ page.title }}</a>
              <p>{{ page.yourField }}</p> <!-- Replace 'yourField' with the actual field name you need -->
            </li>
          </ul>
        </div>
      `,
      methods: {
        // Ensure you handle how to fetch and process pages correctly
        fetchPageData() {
          // Assuming 'luoghi' field contains page references; adjust as needed
          return this.luoghi.map(page => {
            return {
              id: page.id,
              url: page.url,
              title: page.title,
              yourField: page.yourField // Replace with the actual field name you need
            };
          });
        }
      }
    },
    imagetext: {
      computed: {
        layout() {
          return this.field("layout").value; // Use .value to access the field value
        },
        image() {
          return this.field("image")[0] || {}; // Access the first file, return empty object if none
        },
        title() {
          return this.field("title").value; // Use .value to access the field value
        },
        text() {
          return this.field("text").value; // Use .value to access the field value
        },
      },
      template: 
        `
        <p>Image + text</p>
        <div class="k-block-type-image-text" :class="'layout-' + layout">
          <div class="image-column" v-if="image.url">
            <img :src="image.url" alt="Image text">
          </div>
          <div class="text-column">
            {{ title }}
            {{ text }}
          </div>
        </div>
        `
    },
    imagetextbuttons: {
      computed: {
        // Access the fields from the blueprint
        title() {
          return this.field("title");
        },
        image() {
          return this.field("image").toFile();
        },
        text() {
          return this.field("text");
        },
        layout() {
          return this.field("layout");
        },
        buttons() {
          return this.field("buttons").toStructure();
        }
      },
      template: `
        <p>App</p>
        <div class="block-app" :class="layout">
          <div v-if="layout === 'image-text'" class="block-app__inner">
            <div class="block-app__image">
              <img v-if="image" :src="image.url" :alt="image.alt">
            </div>
            <div class="block-app__text">
              <h2>{{ title }}</h2>
              <div v-html="text"></div>
            </div>
          </div>
          <div v-if="layout === 'text-image'" class="block-app__inner">
            <div class="block-app__text">
              <h2>{{ title }}</h2>
              <div v-html="text"></div>
            </div>
            <div class="block-app__image">
              <img v-if="image" :src="image.url" :alt="image.alt">
            </div>
          </div>
          <div class="block-app__buttons">
            <a v-for="button in buttons" :key="button.link" :href="button.link" class="button">
              <img v-if="button.image" :src="button.image.url" alt="">
              {{ button.link }}
            </a>
          </div>
        </div>
      `,
      methods: {
        // Optional: methods for additional processing
        fetchPageData() {
          // No additional processing required for this block as fields are directly used in template
        }
      }
    },
    slider:{
      data() {
        return {
          text: "No text value"
        };
      },
      computed: {
        pages() {
            return this.content.pages || {};
        },
      },
      template: `
      <div @dblclick="open">           
        <h2 class="k-block-type-card-heading">Slider</h2>
      </div>
    `
    },  
  }
});