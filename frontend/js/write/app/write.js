// Sanitise everything besides the below
const editor = null;
let sanitizerConfig = {
        b: {}, // leave <b> without attributes
        strong: {}, // leave <strong> without attributes
        i: {}, // leave <i> without attributes
        em: {}, // leave <em> without attributes
        u: {}, // leave <u> without attributes
        p: {} // leave <p> without attributes
    };

$(function() {

    const storyData = typeof editorStory !== 'undefined' ? editorStory : null;

    editor = new EditorJS({
        // Create a holder for the Editor and pass its ID
        holder: 'editor',

        // Place holder content
        placeholder: "Let's write an awesome story!",

        // Available Tools list. Pass Tool's class or Settings object for each Tool you want to use
        tools: {
            header: {
                class: Header,
                inlineToolbar : true,
                shortcut: 'CMD+SHIFT+H'
            },
            quote: {
                class: Quote,
                inlineToolbar: true,
                shortcut: 'CMD+SHIFT+O',
                config: {
                    quotePlaceholder: 'Enter a quote',
                    captionPlaceholder: 'Quote by'
                },
            },
            delimiter: Delimiter
        },

        // Previously saved data that should be rendered
        data: storyData
    });

    // Editor is ready
    editor.isReady.then(() => {
        $('.write-load').addClass('--loaded');
    })
    .catch((reason) => {
        alert(`Editor.js initialization failed because of ${reason}`);
    });

    // Editor save
    setTimeout(function(){
        editor.save().then((savedData) => {
            const cleanStory = editor.sanitizer.clean(savedData, sanitizerConfig);
            console.log('Story saved: ', cleanStory);
        }).catch((error) => {
            console.log('Saving failed: ', error)
        });
    }, 1000);

});
