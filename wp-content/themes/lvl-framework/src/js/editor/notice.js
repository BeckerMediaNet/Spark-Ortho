class NoticeManager {
    constructor() {
        this.h1Found = false;
        this.isSaving = false;
        this.postTypes = ['page'];
        this.init();
    }

    init() {
        wp.data.subscribe(this.handleSave.bind(this));
    }

    handleSave() {
        const editor = wp.data.select('core/editor');
        const postType = editor.getCurrentPostType();

        if (!this.isSaving && editor.isSavingPost() && !editor.isAutosavingPost()) {
            this.isSaving = true;
        }

        if (this.isSaving && !editor.isSavingPost() && !editor.isAutosavingPost()) {
            this.isSaving = false;

            if (this.postTypes.includes(postType)) {
                this.checkH1Notice();
            }
        }
    }

    checkH1Notice() {
        this.h1Found = document.querySelector('.editor-styles-wrapper .is-root-container h1') !== null;

        wp.data.select('core/editor').getBlocks().forEach(this.checkForH1.bind(this));

        if (this.h1Found) {
            wp.data.dispatch('core/notices').removeNotice('lvl-missing-h1');
        } else {
            wp.data.dispatch('core/notices').createNotice(
                'error',
                'This page is missing a H1 tag. Excluding this will hurt your SEO value. A Banner is a good place to put your H1, or you can add a H1 Heading Block to the content.',
                {
                    isDismissible: true,
                    id: 'lvl-missing-h1',
                    onDismiss: this.removeH1Notice.bind(this),
                }
            );
        }
    }

    checkForH1(block) {
        if ((block.name === 'core/heading' || block.name === 'core/title') && block.attributes.level === 1) {
            this.h1Found = true;
        }

        if (block.name === 'lvl/breadcrumb' && (block.attributes?.data?.header_level === 'h1' || block.attributes?.data?.field_breadcrumb_header_level === 'h1')) {
            this.h1Found = true;
        }

        if (block.innerBlocks.length > 0) {
            block.innerBlocks.forEach(this.checkForH1.bind(this));
        }
    }

    removeH1Notice() {
        wp.data.dispatch('core/notices').removeNotice('lvl-missing-h1');
    }
}

new NoticeManager();