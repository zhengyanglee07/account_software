/**
 * Get preset-root width.
 *
 * Note: this function is unstable, might change in the future
 *
 * @returns {number}
 */
export const getPresetRootWidthInPx = () => {
    const presetRoot = document.querySelector('.eb-preview-root-container');
    return parseInt(getComputedStyle(presetRoot).getPropertyValue('width'));
};

/**
 * Primarily used to set width of some components in email builder
 * preview accordingly to responsive mode (desktop/tablet/mobile)
 *
 * @param responsiveMode Current responsive mode (desktop/tablet/mobile)
 * @returns {string}
 */
export const getResponsiveModeWidth = (responsiveMode) =>
    responsiveMode === 'desktop' ? '600px' : '100%';

/**
 * Replace <p> or <h1-6> tags in content to tag
 *
 * @param {string} rawHtml
 * @param {string} tag
 * @returns {string}
 */
export const replaceHPTags = (rawHtml, tag) => {
    const openTagRegex = /(<(p)>|<(p) [^>]+>|<(h[1-6])>|<(h[1-6]) [^>]+>)/g;
    const closeTagRegex = /(<\/p>|<\/h[1-6]>)/g;

    if (!rawHtml) {
        return `<${tag}></${tag}>`;
    }

    if (openTagRegex.exec(rawHtml).find((v) => v === tag)) {
        return rawHtml;
    }

    return rawHtml
        .replace(openTagRegex, `<${tag}>`)
        .replace(closeTagRegex, `</${tag}>`);
};
