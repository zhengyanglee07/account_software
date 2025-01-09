/* eslint no-param-reassign: "off" */
import clone from 'clone';
import { nanoid } from 'nanoid';
import * as defaults from '@builder/assets/js/builder.js';

let uuid = null;
let editableDesign = 'pageDesign';

export default {
    /**
     * Drag and Drop
     */
    toggleOnDragging(state, bool) {
        state.isOnDragging = bool;
    },

    updateDragSource(
        state,
        {
            group,
            elementName = null,
            elementId = null,
            fromSectionId = null,
            fromColumnId = null,
            fromIndex = null,
            isInner = null,
        }
    ) {
        state.dragSource = {
            group,
            elementName,
            elementId,
            fromSectionId,
            fromColumnId,
            fromIndex,
            isInner,
        };
    },

    /**
     * Remove nullish columns and elements to avoid reorder issue
     */
    purgeUnusedColumnAndElement(state) {
        const { sections, columns, elements } = state[editableDesign];
        const columnArr = Object.keys(columns);
        const elementArr = Object.keys(elements);

        // loop through all sections available
        sections.allIds.forEach((section) => {
            const tempArray = [];
            sections[section].columns.forEach((column) => {
                const columnIdInString = String(column);
                if (
                    columnArr.includes(columnIdInString) &&
                    columns[columnIdInString]
                ) {
                    tempArray.push(columnIdInString);
                }
            });
            sections[section].columns = tempArray;
        });

        columnArr.forEach((id) => {
            const tempArray = [];
            columns[id].elements.forEach((element) => {
                const elementIdInString = String(element);
                if (
                    elementArr.includes(elementIdInString) &&
                    elements[elementIdInString]
                ) {
                    tempArray.push(elementIdInString);
                }
            });
            columns[id].elements = tempArray;
        });
    },

    removeDraggingSection(state, sectionId) {
        const index = state[editableDesign].sections.allIds.findIndex(
            (e) => e == sectionId // eslint-disable-line eqeqeq
        );
        state[editableDesign].sections.allIds.splice(index, 1);
    },

    sectionReorder(state, { from, toSectionId, toIndex }) {
        const index = state[editableDesign].sections.allIds.findIndex(
            (e) => e === toSectionId
        );
        state[editableDesign].sections.allIds.splice(
            toIndex ?? index + 1,
            0,
            from
        );
    },

    reorderColumn: (state, { toSectionId, toIndex, id }) => {
        const { group, fromSectionId, fromIndex, isInner } = state.dragSource;
        if (group !== 'column') return;

        if (fromSectionId !== toSectionId && isInner) return;

        let columnId = null;
        const source = isInner ? 'elements' : 'sections';
        const sectionId = isInner ? fromSectionId : toSectionId;

        if (fromIndex !== undefined && fromIndex !== null) {
            [columnId] = state[editableDesign][source][
                fromSectionId
            ].columns.splice(fromIndex, 1);
        }

        if (toIndex !== undefined && toIndex !== null) {
            state[editableDesign][source][sectionId]?.columns.splice(
                toIndex,
                0,
                columnId ?? id
            );
        }
    },

    reorderElement: (
        state,
        { fromColumnId, fromIndex, toNewColumn, toNewColumnIndex, id }
    ) => {
        let elementId = null;

        if (fromIndex !== undefined && fromIndex !== null) {
            [elementId] = state[editableDesign].columns[
                fromColumnId
            ].elements.splice(fromIndex, 1);
        }

        const elementList =
            typeof toNewColumn === 'string'
                ? state[editableDesign].columns[toNewColumn].elements
                : toNewColumn;

        if (toNewColumnIndex !== undefined && toNewColumnIndex !== null) {
            uuid = elementId ?? id;
            elementList.splice(toNewColumnIndex, 0, elementId ?? id);
        }
    },

    /**
     * CRUD of section
     */
    addSection(state, { id, index, isAddTopSection = false }) {
        uuid = nanoid();
        const { sections } = state[editableDesign];
        const section =
            index && !isAddTopSection ? sections[id] : defaults.section;
        sections[uuid] = clone({
            id: uuid,
            ...section,
        });
        sections.allIds.splice(index ?? sections.allIds.length, 0, uuid);
    },

    duplicateSection(state, { id, index, isInner, columnId }) {
        uuid = nanoid();
        const { sections, columns, elements } = state[editableDesign];
        const newSection = clone({
            ...(isInner ? elements[id] : sections[id]),
            id: uuid,
        });

        const replaceDuplicatedElementIds = (section) => {
            section.columns.forEach((colId, colIndex, array) => {
                const elementIds = [];
                const parentColumn = columns[colId];
                if (!parentColumn) return;
                (parentColumn?.elements ?? []).forEach((elemId) => {
                    const newElemId = nanoid();
                    elements[newElemId] = clone({
                        ...elements[elemId],
                        id: newElemId,
                    });
                    if (elements[elemId].isInner) {
                        replaceDuplicatedElementIds(elements[newElemId]);
                    }
                    elementIds.push(newElemId);
                });
                const newColId = nanoid();
                columns[newColId] = clone({
                    ...parentColumn,
                    id: newColId,
                    elements: elementIds,
                });
                array.splice(colIndex, 1, newColId);
            });
        };

        replaceDuplicatedElementIds(newSection);

        if (isInner) {
            elements[uuid] = clone(newSection);
            columns[columnId].elements.splice(index + 1, 0, uuid);
        } else {
            sections[uuid] = clone(newSection);
            sections.allIds.splice(index + 1, 0, uuid);
        }
    },

    deleteSection(state, { id, columnId, index, isInner }) {
        const { sections, columns, elements } = state[editableDesign];
        if (isInner) {
            const colsToDelete = elements[id].columns;
            colsToDelete.forEach((colId) => {
                const cols = columns[colId];
                cols.elements.forEach((eleId) => {
                    delete elements[eleId];
                });
                delete columns[colId];
            });
            columns[columnId].elements.splice(index, 1);
            delete elements[id];
        } else {
            delete sections[id];
            sections.allIds.splice(index, 1);
        }
    },

    toggleAddTopSectionContainer(state, { id, bool = false }) {
        state[editableDesign].sections[id].settings.showContainer = bool;
    },

    /**
     * CRUD of column
     */
    addColumn(
        state,
        { sectionId = uuid, index, ratios = [30], isInnerColumn }
    ) {
        ratios.forEach((ratio) => {
            const newId = nanoid();
            const columnDefault = clone({
                id: newId,
                ...defaults.column,
                isInner: isInnerColumn,
                settings: {
                    ...defaults.column.settings,
                    col: [ratio, ratio, 100],
                },
            });

            state[editableDesign].columns[newId] = columnDefault;

            if (isInnerColumn) {
                state[editableDesign].elements[sectionId].columns.push(newId);
                return;
            }

            uuid = newId;
            const columnArray =
                state[editableDesign].sections[sectionId].columns;

            if (typeof index === 'number') {
                columnArray.splice(index + 1, 0, uuid);
            } else {
                columnArray.push(uuid);
            }
        });
    },

    duplicateColumn(state, { sectionId, id, index, isInner }) {
        uuid = nanoid();
        const elementIds = [];
        const { sections, columns, elements } = state[editableDesign];

        // TODO Darren combine the function here with the one in duplicateSection
        const replaceDuplicatedElementIds = (section) => {
            section.columns.forEach((colId, colIndex, array) => {
                const duplicatedElementIds = [];
                const parentColumn = columns[colId];
                if (!parentColumn) return;
                (parentColumn?.elements ?? []).forEach((elemId) => {
                    const newElemId = nanoid();
                    elements[newElemId] = clone({
                        ...elements[elemId],
                        id: newElemId,
                    });
                    if (elements[elemId].isInner) {
                        replaceDuplicatedElementIds(elements[newElemId]);
                    }
                    duplicatedElementIds.push(newElemId);
                });
                const newColId = nanoid();
                columns[newColId] = clone({
                    ...parentColumn,
                    id: newColId,
                    elements: duplicatedElementIds,
                });
                array.splice(colIndex, 1, newColId);
            });
        };

        columns[id].elements.forEach((elementId) => {
            const newId = nanoid();
            elements[newId] = clone({
                ...elements[elementId],
                id: newId,
            });
            if (elements[newId].isInner) {
                replaceDuplicatedElementIds(elements[newId]);
            }
            elementIds.push(newId);
        });
        columns[uuid] = clone({
            ...columns[id],
            id: uuid,
            elements: elementIds,
        });
        if (isInner) elements[sectionId].columns.splice(index + 1, 0, uuid);
        else sections[sectionId].columns.splice(index + 1, 0, uuid);
    },

    deleteColumn(state, { sectionId, id, isInner }) {
        const { columns, elements } = state[editableDesign];
        const editSectionType = isInner ? 'elements' : 'sections';
        const column = columns[id];

        column.elements.forEach((elementId) => {
            delete elements[elementId];
        });

        delete columns[id];

        const { columns: sectionColumns } =
            state[editableDesign][editSectionType][sectionId];
        const columnIndex = sectionColumns.findIndex((col) => col === id);
        sectionColumns.splice(columnIndex, 1);
    },

    /**
     * CRUD of element
     */
    addElement(state, { element, elementIndex, columnId }) {
        uuid = nanoid();
        state[editableDesign].columns[columnId].elements.splice(
            elementIndex,
            0,
            uuid
        );
        state[editableDesign].elements[uuid] = {
            id: uuid,
            ...clone(element),
        };
    },

    updateElement(
        state,
        {
            setting,
            value,
            index,
            settingType,
            responsiveSettingIndex,
            id = state.onEditElement.id,
            category,
        }
    ) {
        let { type, name } = state.onEditElement; // eslint-disable-line prefer-const
        const isThemeBuilder = type === 'theme' && name;
        if (isThemeBuilder && name.toLowerCase().includes('button'))
            name = 'button';
        const settingObject = isThemeBuilder
            ? state.themeStyles[name.toLowerCase()][category]
            : state[editableDesign][type || category][id][settingType];
        if (index === null || index === undefined) {
            if (
                !isThemeBuilder &&
                Object.keys(settingObject ?? {}).length === 0
            ) {
                state[editableDesign][type || category][id][settingType] = {};
            }
            if (isThemeBuilder) {
                settingObject[setting] = value;
            } else {
                state[editableDesign][type || category][id][settingType][
                    setting
                ] = value;
            }
            return;
        }
        if (typeof index === 'string') {
            settingObject[setting][index] = value;
            return;
        }
        let oldValue = null;
        if (!settingObject?.[setting]) {
            const defaultElementAttributes = Object.values(defaults).find(
                (el) => el.name === name
            );
            settingObject[setting] =
                defaultElementAttributes?.[settingType]?.[setting] ?? [];
        }
        const settingToEdit = settingObject?.[setting];
        const newValue =
            value === undefined
                ? settingToEdit[responsiveSettingIndex - 1]
                : value;
        for (let i = responsiveSettingIndex; i <= 2; i += 1) {
            if (i === responsiveSettingIndex) {
                oldValue = settingToEdit?.[i] ?? null;
            }
            if (
                settingToEdit?.[i] === oldValue ||
                (settingObject[setting] ?? []).length < 3 ||
                i === 0
            ) {
                settingObject[setting][i] = newValue;
            }
        }
    },

    duplicateElement(state, { id, columnId, elementIndex }) {
        uuid = nanoid();
        state[editableDesign].elements[uuid] = clone({
            ...state[editableDesign].elements[id],
            id: uuid,
        });
        state[editableDesign].columns[columnId].elements.splice(
            elementIndex + 1,
            0,
            uuid
        );
    },

    deleteElement(state, { id, columnId, elementIndex }) {
        state[editableDesign].columns[columnId].elements.splice(
            elementIndex,
            1
        );
        delete state[editableDesign].elements[id];
    },

    /**
     * CRUD of list item
     */
    // addListItem(state, settingName) {
    //     const { id, name } = state.onEditElement;
    //     const listItem = listItemDefault[camelizeString(name)];
    //     state[editableDesign].elements[id].settings[settingName].push({
    //         ...clone(listItem),
    //         id: nanoid(),
    //     });
    // },

    updateListItem(state, { settingName, index, setting, value }) {
        // value is the setting that the settigns should edit, default is listItems
        const { id } = state.onEditElement;

        const element = state[editableDesign].elements[id]?.settings;
        if (!setting && index === null) element[settingName] = value;
        if (!setting && index !== null) element[settingName][index] = value;
        if (setting && index !== null) {
            element[settingName][index][setting] = value;
        }
    },

    duplicateListItem(state, { settingName, index }) {
        const { id } = state.onEditElement;
        const sourceSetting =
            state[editableDesign].elements[id].settings[settingName];
        sourceSetting.splice(index + 1, 0, {
            ...clone(sourceSetting[index]),
            id: nanoid(),
        });
    },

    deleteListItem(state, { settingName, index }) {
        const { id } = state.onEditElement;
        state[editableDesign].elements[id].settings[settingName].splice(
            index,
            1
        );
    },

    // temp for products
    reorderListItems(
        state,
        { settingName, newArray, orderBy, orderSequence, allProducts }
    ) {
        const { id } = state.onEditElement;
        let ft;
        let st = '';

        const saleQuantity = (a, b) => {
            const x =
                allProducts.find((e) => e.id === a?.id)?.saleQuantity ?? 0;
            const y =
                allProducts.find((e) => e.id === b?.id)?.saleQuantity ?? 0;
            if (x < y) {
                return -1;
            }
            if (x > y) {
                return 1;
            }
            return 0;
        };
        const itemSorting =
            orderBy === 'sales'
                ? newArray.sort(saleQuantity)
                : newArray.sort((a, b) => {
                      if (orderBy === 'productTitle') {
                          ft = a[orderBy];
                          st = b[orderBy];
                      }
                      if (orderBy === 'productPrice') {
                          ft = +a[orderBy].replace(',', '');
                          st = +b[orderBy].replace(',', '');
                      }

                      if (ft < st) {
                          return -1;
                      }

                      if (st > ft) {
                          return 1;
                      }
                      return 0;
                  });
        let itemReverseSorting = [];
        if (orderSequence === 'Ascending') {
            itemReverseSorting = itemSorting;
        } else if (orderSequence === 'Descending') {
            itemReverseSorting = itemSorting.reverse();
        } else {
            itemReverseSorting = newArray;
        }

        state[editableDesign].elements[id].settings[settingName] =
            itemReverseSorting;
    },

    //* Images
    setImageSnapshotStatus(state, status) {
        state.previewImageSnapshot = {
            isTaking: status,
            queryString: new Date().getTime(),
        };
    },

    /**
     * Builder Configurations
     */
    setBuilderSettings(state, builderSettings) {
        state.builderSettings = builderSettings;
    },

    setPopupSettings(state, popupSettings = {}) {
        state.popupSettings = {
            ...popupSettings,
            configurations:
                popupSettings?.configurations ??
                state.popupConfigurationsDefault,
        };
    },

    setFunnelSettings(state, funnelSettings) {
        state.funnelSettings = funnelSettings;
    },

    setInitialDesign(state, { type, design }) {
        const { sections, columns, elements } = JSON.parse(design ?? '{}');
        const builderDesign = {
            sections: sections ?? { allIds: [] },
            columns: Array.isArray(columns) || !columns ? {} : columns,
            elements: Array.isArray(elements) || !elements ? {} : elements,
        };
        state[type] =
            state.mode === 'Published'
                ? Object.freeze(builderDesign)
                : clone(builderDesign);
    },

    setIsDefaultPage(state, isDefaultPage) {
        state.isDefaultPage = isDefaultPage;
    },

    setPopups(state, popups) {
        state.popups = popups;
    },

    updateSavedPopup(state, updatedPopup = {}) {
        const index = (state.popups ?? []).findIndex(
            (popup) => popup.id === updatedPopup?.id
        );
        state.popups[index] = {
            ...updatedPopup,
            design: JSON.stringify(updatedPopup?.design),
        };
    },

    setPublishDomain(state, domain) {
        state.publishDomain = domain;
    },

    setMode(state, mode = 'Builder') {
        state.mode = mode;
    },

    setPageType(state, type) {
        state.pageType = type ?? 'page';
        editableDesign = ['header', 'footer', 'page', 'popup'].includes(type)
            ? `${type}Design`
            : 'pageDesign';
    },

    setOnEditElement(state, { id, name, type = 'elements' }) {
        state.onEditElement = !name
            ? {
                  id: null,
                  name: null,
                  type,
              }
            : {
                  id: id ?? uuid,
                  name,
                  type,
              };
    },

    toggleLeftbarCollapse(state, bool) {
        state.isLeftPanelCollapse = bool;
    },

    setResponsiveMode(state, mode) {
        state.responsiveMode = mode;
    },

    /**
     * Undo Redo
     */

    setInitialUndoRedo(state) {
        const s = clone(state);
        const currentPage = `${s.pageType}Design`;
        state.undoRedo.current = {
            builderSettings: s.builderSettings,
            [currentPage]: s[currentPage],
        };
    },

    recordHistory(state, { postState }) {
        const { pageType } = postState;

        const currentPage = pageType;
        const { builderSettings } = state;
        const newChanges = clone({
            builderSettings,
            [currentPage]: state[currentPage],
        });
        // set current change (old) to the record
        const oldChanges = state.undoRedo.current;

        // done = items that are able to be undo
        // undone = items that are able to redo
        state.undoRedo.current = newChanges;
        state.undoRedo.done.push(oldChanges);
        // remove redo list when new action is done        // remove undone when new action is done
        state.undoRedo.undone = [];
    },

    updateHistory(state, type) {
        const changeLog = state.undoRedo;
        if (type === 'undo') {
            changeLog.undone.push(changeLog.current);
            changeLog.current = changeLog.done.pop();
        } else {
            changeLog.done = [...changeLog.done, changeLog.current];
            const newRedoChange = changeLog.undone.pop();
            changeLog.current = newRedoChange;
        }

        const historyKeys = Object.keys(changeLog.current);
        historyKeys.forEach((key) => {
            state[key === 'builderSettings' ? `builderSettings` : key] = clone(
                changeLog.current[key]
            );
        });
    },

    //* Update Builder Configurations
    updateBuilderSettings(state, { setting, value }) {
        state.builderSettings[setting] = value;
    },

    updatePageFonts(state, { setting, value }) {
        if (!state.builderSettings.fonts) {
            state.builderSettings.fonts = {};
        }
        const { type, id, name } = state.onEditElement;
        const key =
            type === 'theme' ? `theme-${name}-${setting}` : `${id}-${setting}`;
        state.builderSettings.fonts[key] = value;
    },

    //* Template
    setUserTemplates(state, templates) {
        state.userTemplates = templates;
    },

    deleteUserTemplate(state, templateId) {
      const index = state.userTemplates.findIndex(
        (template) => template.id === templateId
      );
      state.userTemplates.splice(index, 1);
    },

    updateFormDetail(state, fd) {
        state.formDetail = fd;
    },

    updateDifferentBillingAddress(state, d) {
        state.differentBillingAddress = d;
    },
    updateShippingFee(state, fees) {
        state.shippingFee = fees;
    },

    setGeneralTemplates(state, templates) {
        state.generalTemplates = templates;
    },

    // TODO Darren => filter unrelated elements, add template on top of specific section
    insertTemplate(state, { template, parentSectionId }) {
        const { sections, columns, elements } = JSON.parse(template);
        Object.entries(sections).forEach(([sectionId, section]) => {
            if (sectionId === 'allIds') return;
            const newSectionId = nanoid();
            const columnIds = [];
            section.columns.forEach((columnId) => {
                const newColumnId = nanoid();
                const elementIds = [];
                if (!columns[columnId]) return;
                columns[columnId].elements.forEach((elementId) => {
                    const id = nanoid();
                    state[editableDesign].elements[id] = {
                        ...clone(elements[elementId]),
                        id,
                    };
                    elementIds.push(id);
                });
                state[editableDesign].columns[newColumnId] = {
                    ...columns[columnId],
                    id: newColumnId,
                    elements: elementIds,
                };
                columnIds.push(newColumnId);
            });
            state[editableDesign].sections[newSectionId] = {
                ...sections[sectionId],
                id: newSectionId,
                columns: columnIds,
            };
            if (parentSectionId) {
                const index = state[editableDesign].sections.allIds.findIndex(
                    (secId) => secId === parentSectionId
                );
                state[editableDesign].sections.allIds.splice(
                    index,
                    0,
                    newSectionId
                );
            } else {
                state[editableDesign].sections.allIds.push(newSectionId);
            }
        });
    },

    //* Tags
    setTags(state, tags) {
        state.tags = tags ?? [];
    },

    //* Update People Profile
    setCustomFields(state, customFields) {
        state.customFields = customFields ?? [];
    },

    setAddCustomFieldLimit(state, customFieldCountLimit) {
        state.customFieldCountLimit = customFieldCountLimit;
    },

    appendNewCustomField(
        state,
        { custom_field_name: customFieldName, type = 'text' }
    ) {
        state.customFields.splice(state.customFields.length, 0, {
            customFieldName,
            type,
        });
        // TODO use a more efficient way
        Object.values(state[editableDesign].elements).forEach((element) => {
            if (element.name === 'Form') {
                element.settings.updatePeopleProfileOptions.push({
                    text: customFieldName,
                    value: customFieldName,
                    index: -1,
                    _type: type,
                });
            }
        });
    },

    updateCustomFieldLists(state, id) {
        const dbCustomFieldLists = state.customFields;
        const dbCustomFieldListValues = dbCustomFieldLists.map(
            (option) => option.custom_field_name
        );
        const updatePeopleProfileLists =
            state[editableDesign].elements[id]?.settings
                ?.updatePeopleProfileOptions;
        if (!updatePeopleProfileLists) return;
        const elementCustomFieldLists = updatePeopleProfileLists
            .slice(14)
            .map((option) => option.value);

        //* append the new cf into updatePeopleProfileOptions of form element if haven't existed
        dbCustomFieldLists.forEach(
            ({ custom_field_name: customFieldName, type }) => {
                if (!elementCustomFieldLists.includes(customFieldName)) {
                    updatePeopleProfileLists.push({
                        text: customFieldName,
                        value: customFieldName,
                        index: -1,
                        _type: type,
                    });
                }
            }
        );

        //* delete the form cf option if it's value doesn't appear inside db custom field lists
        elementCustomFieldLists.forEach((cfName, index) => {
            if (!dbCustomFieldListValues.includes(cfName)) {
                updatePeopleProfileLists.splice(index + 12, 1);
            }
        });
    },

    updatePopupSettings(state, { settingName, value, index }) {
        const { configurations } = state.popupSettings;

        if (index === null || index === undefined) {
            configurations[settingName] = value;
            return;
        }

        if (!configurations?.[settingName]) {
            configurations[settingName] = [];
        }

        const settingToEdit = configurations[settingName];
        let oldValue = null;
        for (let i = index; i <= 2; i += 1) {
            if (i === index || !settingToEdit[i]) {
                oldValue = settingToEdit[i] ?? null;
                settingToEdit[i] = value;
            } else if (settingToEdit[i] === oldValue) {
                configurations[settingName][i] = value;
            }
        }
    },
};
