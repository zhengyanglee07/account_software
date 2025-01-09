/* eslint-disable indent */
import cloneDeep from 'lodash/cloneDeep';
import isArray from 'lodash/isArray';

// =============================================================================
// Note: the obj below is here for testing purpose. Just seen it :)
// const test = [
//     {
//         id: 'preset-root',
//         children: [
//             {
//                 tagName: 'a',
//                 content: 'Click Me',
//                 id: 'RZAjQmeZi',
//             },
//             {
//                 tagName: 'preview-text-editor',
//                 attributes: {
//                     attrs: {
//                         'font-size': '16px',
//                         'font-weight': 'bold',
//                     },
//                 },
//                 content: '<p>Write something</p>',
//                 id: 'nmqSn5nzR',
//             },
//             {
//                 tagName: 'a',
//                 content: 'Click Me',
//                 id: 'UZmtv2_O7',
//             },
//             {
//                 tagName: 'preview-text-editor',
//                 attributes: {
//                     attrs: {
//                         'font-size': '20px',
//                     },
//                 },
//                 content: '<p>Write something</p>',
//                 id: 'N_nAROrry',
//             },
//             {
//                 tagName: 'div',
//                 children: [
//                     {
//                         tagName: 'preview-text-editor',
//                         content: '<p>Write something</p>',
//                         id: 'testId',
//                     },
//                     {
//                         tagName: 'preview-text-editor',
//                         content: '<p>Write something</p>',
//                         id: 'aNC8Y1VuZ',
//                     },
//                 ],
//                 id: 'khEdNDEbd',
//             },
//         ],
//     },
//
//     // next
//     {
//         tagName: 'div',
//         children: [
//             {
//                 tagName: 'div',
//                 content: '<p>Write something</p>',
//                 children: [
//                     {
//                         tagName: 'gg',
//                         content: '<p>Write something</p>',
//                         id: 'testId',
//                     },
//                     {
//                         tagName: 'preview-text-editor',
//                         content: '<p>Write something</p>',
//                         id: 'aNC8Y1VuZ',
//                     },
//                 ],
//             },
//             {
//                 tagName: 'next-text-editor',
//                 content: '<p>Write something</p>',
//                 id: 'testId',
//             },
//         ],
//         id: 'khEdNDEbd',
//     },
// ];
// =============================================================================

/**
 * Deep search an object with a predicate func
 *
 * Note: object returned by this function references the original root object.
 *       Hence, if you modified any property on the returned object, the
 *       root/original object will be modified also
 *
 * @example
 * // simple lookup
 * const objFound = deepSearch(oriObj, 'id', (k, v) => v === 'id-to-find');
 *
 * // perform some mutation on the objFound
 * const oriObj = { a: 1, b: 2 };
 * const objFound = deepSearch(oriObj, 'a', (k, v) => v === 1);  // { a: 1, b: 2 }
 * objFound.b = 'new value';
 * console.log(objFound); // { a: 1, b: 'new value' }
 * console.log(oriObj);   // { a: 1, b: 'new value' }
 *
 * // of course, this function works on deeply nested object, as long as your nested
 * // child's key is 'children'
 * const oriObj = {
 *   a: 1,
 *   children: [
 *     {
 *         a: 2,
 *         children: []
 *     }
 *   ]
 * };
 * const objFound = deepSearch(oriObj, 'a', (k, v) => v === 2);  // { a: 2, children: [] }
 *
 * @param obj Base object
 * @param key Property key
 * @param predicate Predicate func with method signature (key, value) => boolean
 * @returns {null|Object}
 */
export const deepSearch = (obj, key, predicate) => {
    if (
        Object.prototype.hasOwnProperty.call(obj, key) &&
        predicate(key, obj[key]) === true
    ) {
        return obj;
    }

    for (let i = 0; i < Object.keys(obj).length; i++) {
        const nextObj = obj[Object.keys(obj)[i]];
        if (nextObj && typeof nextObj === 'object') {
            const o = deepSearch(nextObj, key, predicate);
            if (o != null) return o;
        }
    }
    return null;
};

/**
 * Deep delete an object property without modifying(mutate) base object.
 * Return modified object if successfully deleted, else return false
 *
 * TODO: write a test suite
 *
 * @example
 *   deepDelete(obj, 'children', 'id', 'my-unique-id');
 *
 * @param obj Base obj/array to delete property
 * @param childrenKey Children key to recursively search
 * @param propertyKey Object property key to delete
 * @param propertyVal Object value to match and delete
 * @returns {Object|boolean}
 */
export const deepDelete = (obj, childrenKey, propertyKey, propertyVal) => {
    const treeToReturn = cloneDeep(obj);
    let modifiedObj = false;

    const recursiveDelete = (obj, childrenKey, propertyKey, propertyVal) => {
        if (Object.prototype.hasOwnProperty.call(obj, childrenKey)) {
            const children = obj[childrenKey];
            for (let i = 0; i < children.length; i++) {
                if (children[i][propertyKey] === propertyVal) {
                    children.splice(i, 1);
                    modifiedObj = true;
                    break;
                }

                if (!modifiedObj) {
                    recursiveDelete(
                        children[i],
                        childrenKey,
                        propertyKey,
                        propertyVal
                    );
                }
            }
        }
    };

    // handle situation where the obj is an array
    if (isArray(treeToReturn) && treeToReturn.length !== 0) {
        for (let i = 0; i < treeToReturn.length; i++) {
            if (!modifiedObj) {
                recursiveDelete(
                    treeToReturn[i],
                    childrenKey,
                    propertyKey,
                    propertyVal
                );
            }
        }
    }

    return modifiedObj ? treeToReturn : false;
};

/**
 * Search and modify oen or more properties on the FIRST found sub-object
 * that matches the first key-value pair specified in 'matcher'.
 * Return modified object if success, else return undefined. For mutate = true,
 * the returned object is optional, since it mutates original object directly.
 *
 * Note2: assume modVal to be the same data type as objFound[modKey].
 *        This function might get wrong if modVal type !== objFound[modKey] type
 *
 * @summary This is a simple wrapper around deepSearch with added modify functionality.
 *          If you want more custom use case, you can always modify/mutate object
 *          returned by deepSearch directly.
 *
 * @example
 * // with mutation
 * deepSearchAndModifyFirst(
 *   obj,
 *   {
 *     id: 'idToSearch'
 *   }, // please aware that only first entry of matcher will be taken
 *   {
 *     content: 'example content',
 *     attributes: {
 *       attrs: {
 *         class: 'custom-css-class',
 *       },
 *       style: {
 *         'font-size': '16px',
 *       },
 *     },
 *   },
 *   {
 *     mutate: true
 *   }
 * );
 *
 * // without mutation (default)
 * const modifiedObject = deepSearchAndModifyFirst(
 *   obj,
 *   {
 *     id: 'idToSearch'
 *   }, // please aware that only first entry of matcher will be taken
 *   {
 *     content: 'example content',
 *     attributes: {
 *       attrs: {
 *         class: 'custom-css-class',
 *       },
 *       style: {
 *         'font-size': '16px',
 *       },
 *     },
 *   },
 * );
 * console.log(isEqual(obj, modifiedObj); // false
 *
 * @see deepSearch
 *
 * Configuration available:
 *   - mutate: Mutate original obj
 *   - returnOri: Return matcher obj
 *   - replaceObj: Replace an entire 'object' property found in original obj instead of using spreading
 *
 * @param obj Original object to modify
 * @param matcher Matcher object
 * @param modifier Modifier object
 * @param config Some extra configurations to apply to this function. For detailed properties refer above
 */
export const deepSearchAndModifyFirst = (
    obj,
    matcher,
    modifier,
    config = {}
) => {
    const {
        // whether to mutate original obj
        mutate = false,

        // whether to return matcher obj before modifying
        returnOri = false,

        // whether to replace entire matcher obj or preserving old properties
        replaceObj = false,
    } = config;

    const [key, val] = Object.entries(matcher)[0] || ['', ''];

    if (!key) {
        throw new Error('Please provide a valid and non-empty key to search');
    }

    const objToSearch = mutate ? obj : cloneDeep(obj);
    const objFound = deepSearch(objToSearch, key, (k, v) => v === val);

    if (!objFound) {
        return;
    }

    const oriObjFound = cloneDeep(objFound);
    for (const [modKey, modVal] of Object.entries(modifier)) {
        if (
            typeof objFound[modKey] === 'object' &&
            !isArray(objFound[modKey]) &&
            !replaceObj
        ) {
            objFound[modKey] = {
                ...objFound[modKey],
                ...modVal,
            };
            continue;
        }

        // normally assign value if objFound[modKey] is not object
        // Note: array is assumed to be completely replaced if using
        //       this function
        objFound[modKey] = modVal;
    }

    return !returnOri
        ? objToSearch
        : {
              ori: oriObjFound,
              obj: objToSearch,
          };
};

/**
 * Modify all the (nested) object that contains with a child of property 'childKey',
 * default to 'children', by calling the modifierFunc provided on each object.
 *
 * modifierFunc should have the following signature:
 * function modifierFunc(objectToModify) {
 *   return undefined;  // no need to return anything
 * }
 *
 * If you don't provide any modifierFunc, then this function just do nothing
 * without prompting any error.
 *
 * @example
 * const rootArr = [{
 *   a: 1,
 *   children: [{
 *     b: 2
 *   }]
 * }];
 * const modArr = deepModifyAll(rootArr, (obj) => {
 *   obj.c = 3;
 * });
 * console.log(rootArr); // [{ a: 1, c: 3, children: [{ b: 2, c: 3 }] }];
 * console.log(modArr);  // [{ a: 1, c: 3, children: [{ b: 2, c: 3 }] }];
 *
 * @param rootArr Root array that contains recursive structure of objects to modify
 * @param modifierFunc Function that applies to each recurring object
 * @param childKey Recurring object's child key, default to 'children'
 * @returns reference to modified root array (optional)
 */
export const deepModifyAll = (
    rootArr,
    modifierFunc = (obj) => obj,
    childKey = 'children'
) => {
    if (!isArray(rootArr)) {
        throw new Error(
            'Please provide an array instead. If your root is an object, please wrap it in an array.'
        );
    }

    for (let i = 0; i < rootArr.length; i++) {
        const objToModify = rootArr[i];
        modifierFunc(objToModify);

        if (Object.prototype.hasOwnProperty.call(objToModify, childKey)) {
            deepModifyAll(objToModify[childKey], modifierFunc);
        }
    }

    return rootArr;
};

/**
 * Note: this method mutates origin obj automatically. Please deep
 *       clone your obj before using
 *
 * Note2: default key for this method is 'id' only. So the val should be
 *        value of 'parent' id, or null/empty for root array
 *
 * @param {*} obj Origin object/array
 * @param {*} val Value of parent "id"
 * @param {*} idxToInsert Index to insert data
 * @param {*} data Data to insert
 * @param {*} config Extra configs (for decision mostly)
 */
export const deepInsert = (obj, val, idxToInsert, data, config = {}) => {
    const key = 'id'; // this method is specialized for inserting with parent id key

    if (!data) {
        throw new Error('No data found to be insert');
    }

    if (!val) {
        obj.splice(idxToInsert, 0, data);
        return obj;
    }

    if (Object.prototype.hasOwnProperty.call(obj, key) && obj[key] === val) {
        if (obj.type === 'decision') {
            const { route } = config;

            if (!route) {
                throw new Error(
                    "Please provide a 'route' option in config param for decision element"
                );
            }

            obj[route].splice(idxToInsert, 0, data);
            return obj;
        }

        obj.splice(idxToInsert, 0, data);
        return obj;
    }

    for (let i = 0; i < Object.keys(obj).length; i++) {
        const nextObj = obj[Object.keys(obj)[i]];
        if (nextObj && typeof nextObj === 'object') {
            const o = deepInsert(nextObj, val, idxToInsert, data, config);
            if (o != null) return o;
        }
    }

    return null;
};

/**
 * Note: this method mutates origin obj automatically. Please deep
 *       clone your obj before using
 *
 * Custom deep delete func for 'id' key only, which is different with
 * deepDelete above.
 *
 * @param {*} obj Origin object
 * @param {*} val Value of 'id' key
 * @param {*} parent Parent if exists, need not to provide at top level caller
 */
export const customDeepDelete = (obj, val, parent = null) => {
    const key = 'id';

    if (Object.prototype.hasOwnProperty.call(obj, key) && obj[key] === val) {
        const idx = parent.findIndex((o, i) => o[key] === val);

        if (idx !== -1) {
            parent.splice(idx, 1);
        }
        return obj;
    }

    for (let i = 0; i < Object.keys(obj).length; i++) {
        const nextObj = obj[Object.keys(obj)[i]];
        if (nextObj && typeof nextObj === 'object') {
            const o = customDeepDelete(nextObj, val, obj);
            if (o != null) return o;
        }
    }

    return null;
};
