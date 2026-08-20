// document.addEventListener('DOMContentLoaded', function () {
(function () {
    const formwrapper = document.querySelector('[data-gator-form]');
    if (!formwrapper) {
        return;
    }

    // mutation observer
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.addedNodes.length) {
                // console.log('addednodes', mutation.addedNodes[0]);
                let element = mutation.addedNodes[0];

                if (element.nodeName === 'FORM') {
                    observer.observe(element, config)
                }


                if (element.nodeName !== 'FORM' && element.nodeName !== 'DIV') {
                    return;
                }

                // console.log('mutation', element);

                let inputs = element.querySelectorAll('input:not([type="submit"])');
                // console.log('inputs', inputs)
                inputs.forEach(input => {
                    input.classList.add('form-control');
                });

                let submit = element.querySelector('input[type="submit"]');
                if (submit) {
                    submit.classList.add('btn');
                    submit.classList.add('btn-primary');
                    submit.classList.add('m-3');
                }

                let selects = element.querySelectorAll('select');
                selects.forEach(select => {
                    select.classList.add('form-select');
                });

                let textareas = element.querySelectorAll('textarea');
                textareas.forEach(textarea => {
                    textarea.classList.add('form-control');
                });

                //success message
                if (element.classList.contains('gf__status-message')) {
                    element.classList.add('border');
                    element.classList.add('rounded');
                    element.classList.add('p-3');
                    element.classList.add('mb-4');

                    if (element.classList.contains('gf__status-message--error')) {
                        element.classList.add('border-warning');
                    }
                    if (element.classList.contains('gf__status-message--success') || element.classList.contains('gf__status-message--succcess')) { // accounting for typo
                        element.classList.add('border-success');
                    }

                    // loop through children and remove color style
                    for (let i = 0; i < element.children.length; i++) {
                        element.children[i].style.color = '';
                    }
                }
            }
        });
    });

    // config for observer
    const config = {
        childList: true
    };

    // start observing
    observer.observe(formwrapper, config);
})();
// });