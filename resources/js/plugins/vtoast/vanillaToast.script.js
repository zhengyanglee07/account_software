/* eslint-disable indent */

/**
 * Credit: https://dev.to/bmsvieira/vanilla-js-fadein-out-2a6o
 *
 * Change opacity decrement value in fade() IIFE below to adjust animation duration
 *
 * Note: cb MUST BE PROVIDED to remove created toast (in our project)
 *
 * @param el
 * @param cb
 */
function fadeOut(el, cb) {
    el.style.opacity = 1;

    (function fade() {
        if ((el.style.opacity -= 0.05) < 0) {
            el.style.display = 'none';
            cb(); // remember to add a callback here for removing elem
        } else {
            requestAnimationFrame(fade);
        }
    })();
}

/**
 * Credit: https://dev.to/bmsvieira/vanilla-js-fadein-out-2a6o
 *
 * Change val increment value in fade() IIFE below to adjust animation duration
 *
 * @param el
 * @param display
 */
function fadeIn(el, display = 'block') {
    el.style.opacity = 0;
    el.style.display = display;

    (function fade() {
        let val = parseFloat(el.style.opacity);
        if (!((val += 0.05) > 1)) {
            el.style.opacity = val;
            requestAnimationFrame(fade);
        }
    })();
}

/**
 * Assign CSS to document element. Simple alternative to JQuery .css()
 *
 * Note: your css object attributes MUST BE camelCase variant, not kebab-case
 *
 * example CSS:
 * {
 *     display: 'block',
 *     borderRadius: '6px',
 *     backgroundColor: 'black'
 * }
 *
 * @param element
 * @param css CSS object (camelCase variant)
 * @returns {*}
 */
function assignCss(element, css) {
    Object.keys(css).forEach(function (attr) {
        element.style[attr] = css[attr];
    });

    return element;
}

(function () {
    window._Toast = function (title, message, type, options) {
        const topNav = document.querySelector('.top-nav');
        const topNavStyle = topNav?.style.display !== 'none';

        const defaultOptions = {
            appendTo: 'body',
            position_class:
                topNav && topNavStyle
                    ? 'toast-top-center--mini-store'
                    : 'toast-bottom-right',
            fullscreen: false,
            width: 250,
            spacing: 20,
            timeout: 4000,
            has_close_btn: true,
            has_icon: true,
            sticky: false,
            border_radius: '6px',
            rtl: false,
        };

        topNav && topNavStyle
            ? (options.position_class = 'toast-top-center--mini-store')
            : null;

        let $element = null;

        let $subElement = null;

        const $options = { ...defaultOptions, ...options };

        let { spacing } = $options;

        const css = {
            position: $options.appendTo === 'body' ? 'fixed' : 'absolute',
            minWidth: $options.width,
            display: 'none',
            borderRadius: $options.border_radius,
            zIndex: 999999,
        };

        $element = document.getElementById('toast-container')
        if(!$element){
            $element = document.createElement('div');
            $element.id = 'toast-container';
            $element.classList.add(
                // 'toast-item-wrapper',
                type,
                $options.position_class
            );
        }

        $subElement = document.createElement('div');
        $subElement.classList.add('toast', `toast-${type}`);

        const toastTitle = document.createElement('p');
        toastTitle.classList.add('toast-title');
        toastTitle.textContent = title;

        const toastMsg = document.createElement('div');
        toastMsg.classList.add('toast-message');
        toastMsg.textContent = message;

        $element.appendChild($subElement);

        if ($options.has_close_btn) {
            const closeBtn = document.createElement('button');
            closeBtn.classList.add('toast-close-button');
            $subElement.appendChild(closeBtn);

            if ($options.rtl) {
                css.paddingLeft = '20px';
            } else {
                css.paddingRight = '20px';
            }
        }
        // $subElement.appendChild(toastTitle);
        $subElement.appendChild(toastMsg);

        if ($options.fullscreen) {
            $element.classList.add('fullscreen');
        }

        if ($options.rtl) {
            $element.classList.add('rtl');
        }

        if ($options.sticky) {
            $options.spacing = 0;
            spacing = 0;

            switch ($options.position_class) {
                case 'toast-top-left': {
                    css.top = 0;
                    css.left = 0;
                    break;
                }
                case 'toast-top-right': {
                    css.top = 0;
                    css.left = 0;
                    break;
                }
                case 'toast-top-center': {
                    css.top = 0;
                    css.left = css.right = 0;
                    css.width = '100%';
                    break;
                }
                case 'toast-bottom-left': {
                    css.bottom = 0;
                    css.left = 0;
                    break;
                }
                case 'toast-bottom-right': {
                    css.bottom = 0;
                    css.right = 0;
                    break;
                }
                case 'toast-bottom-center': {
                    css.bottom = 0;
                    css.left = css.right = 0;
                    css.width = '100%';
                    break;
                }
                default: {
                    break;
                }
            }
        }
        document
            .querySelector($options.appendTo)
            .appendChild(assignCss($element, css));

        fadeIn($element);

        function removeToast($elem = null) {
            window._Toast.remove($elem ?? $element);
        }

        if ($options.timeout > 0) {
            setTimeout(() => {
                removeToast($subElement)
            }, $options.timeout);
        }
        const allCloseButton = $element.querySelectorAll('.toast-close-button');
        allCloseButton.forEach(close => {
            close.addEventListener('click', ($event) => {
                removeToast($event.target.parentNode)
            });
        })

        return $element;
    };

    window._Toast.remove = function ($element) {
        'use strict';
        fadeOut($element, function () {
            $element.remove();
        });
    };
})();
