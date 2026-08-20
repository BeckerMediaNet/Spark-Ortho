// Theme settings
// import theme from '../../theme.json';
const theme = app_localized?.themeJson;
import './components/colors';
import './editor/notice';
import './editor/block-hints';
import './editor/general';

import './wp-blocks/attributes';
import './wp-blocks/content-columns';
// import './wp-blocks/experimental'; // enable wp "experimental" options
// import './wp-blocks/list';
import './wp-blocks/spacer';
import './wp-blocks/visibility';
import './wp-blocks/columns';
import './wp-blocks/section-wrapper';

document.addEventListener('DOMContentLoaded', (e) => {
    function setEditorWidthVariable() {
        const editor = document.querySelector('.block-editor-writing-flow') || document.querySelector('.wp-block-post-content');
        if (editor) {
            const editorWidth = editor.offsetWidth;
            document.body.style.setProperty('--editor-width', `${editorWidth}px`);
        }
    }

    function initEditorWidthObserver() {
        const editor = document.querySelector('.block-editor-writing-flow') || document.querySelector('.wp-block-post-content');

        if (editor) {
            setEditorWidthVariable(); // Set initial width

            const resizeObserver = new ResizeObserver((entries) => {
                for (let entry of entries) {
                    if (entry.target === editor) {
                        setEditorWidthVariable();
                    }
                }
            });

            resizeObserver.observe(editor);
        } else {
            
            const observer = new MutationObserver((mutations, obs) => {
                const editor = document.querySelector('.block-editor-writing-flow') || document.querySelector('.wp-block-post-content');

                if (editor) {
                    obs.disconnect();
                    initEditorWidthObserver();
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            // Modern WP editors render content inside an iframe, so this selector may
            // never appear at the top level. Stop watching after a while instead of
            // observing the entire document body for the rest of the editing session --
            // a long-lived observer here was interacting badly with pattern/template
            // preview modals (rapid iframe mount/unmount), causing a ResizeObserver
            // crash in core's own block-editor.min.js.
            setTimeout(() => observer.disconnect(), 15000);
        }
    }

    setTimeout(initEditorWidthObserver, 1000);
});