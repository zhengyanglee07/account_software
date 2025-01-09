import isEmpty from 'lodash/isEmpty';
import isArray from 'lodash/isArray';
import isString from 'lodash/isString';
import cloneDeep from 'lodash/cloneDeep';

/**
 * Create component is a recursive function that takes a DOM structure
 * and a rendering function (of vue.js) and returns a Vue  component.
 */
export const createComponent = (dNode, h) => {
    // Handle empty elements and return empty array in case the dNode passed in is empty
    if (isEmpty(dNode)) {
        return [];
    }
    // if the el is array call createComponent for all nodes
    if (isArray(dNode)) {
        return dNode.map((child) => createComponent(child, h));
    }
    let children = [];

    if (dNode.children && dNode.children.length > 0) {
        dNode.children.forEach((c) => {
            if (isString(c)) {
                children.push(c);
            } else {
                children.push(createComponent(c, h));
            }
        });
    }
    // Need to clone
    const attributes = cloneDeep(dNode.attributes);
    return h(
        dNode.tagName,
        attributes,
        children.length > 0 ? children : dNode.content
    );
};
