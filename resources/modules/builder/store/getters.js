import clone from 'clone';

export default {
    allSections:
        (state) =>
        (sectionType = 'page') => {
            const design = state[`${sectionType}Design`];
            return design?.sections?.allIds
                .map((id) => design.sections[id])
                .filter((e) => e);
        },

    allPopups: (state) => {
        return state.popups.reduce((acc, popup) => {
            const design = JSON.parse(popup.design ?? '{}');
            acc[popup.reference_key] = design?.sections?.allIds
                .map((id) => design.sections[id])
                .filter((e) => e);
            return acc;
        }, {});
    },

    pageBuilderEditableDesign:
        ({ mode, pageType }) =>
        (sectionType) => {
            const notOriginalPageType = ['landing', 'template'].includes(
                pageType
            );
            if (pageType === 'popup') return 'popupDesign';
            if (mode !== 'Builder' || (sectionType && !notOriginalPageType))
                return `${sectionType}Design`;
            return `${notOriginalPageType ? 'page' : pageType}Design`;
        },

    dataSource: (state, getters) => (sectionType, popupRefKey) => {
        if (sectionType === 'popup-modal' && popupRefKey) {
            const popupDesign = state.popups.find(
                (popup) => popup.reference_key === popupRefKey
            )?.design;
            return JSON.parse(popupDesign ?? '{}');
        }
        return state[getters.pageBuilderEditableDesign(sectionType)];
    },

    getSectionColumns:
        (state, getters) =>
        ({
            columnIds,
            sectionType = 'page',
            isReverse = false,
            popupRefKey = null,
        }) => {
            const ids = isReverse ? columnIds.reverse() : columnIds;
            return ids
                .map(
                    (id) =>
                        getters.dataSource(sectionType, popupRefKey).columns[id]
                )
                .filter((e) => e);
        },

    getColumnElements:
        (state, getters) =>
        (elementIds, sectionType = 'page', popupRefKey = null) =>
            elementIds
                .map(
                    (id) =>
                        getters.dataSource(sectionType, popupRefKey).elements[
                            id
                        ]
                )
                .filter((e) => e),

    navigatorElements: (state, getters) => {
        const {
            sections: allSections,
            columns: allColumns,
            elements: allElements,
        } = state[getters.pageBuilderEditableDesign()];

        return allSections.allIds.map((sectionId) => {
            const { columns: columnIds } = clone(allSections[sectionId]);
            const columns = columnIds.map((columnId) => {
                const { elements: elementIds, isInner: isInnerColumn } = clone(
                    allColumns[columnId]
                );
                const elements = elementIds.map((elementId) => {
                    const { name: elementName, isInner: isInnerSection } =
                        clone(allElements[elementId]);
                    return {
                        id: elementId,
                        name: elementName,
                        isInner: isInnerSection,
                    };
                });
                return {
                    id: columnId,
                    name: 'Column',
                    isInner: isInnerColumn,
                    elements,
                };
            });
            return {
                id: sectionId,
                name: 'Section',
                columns,
            };
        });
    },

    onEditElementAttributes: (state, getters) => {
        const { id, type } = state.onEditElement;
        const editableDesign = getters.pageBuilderEditableDesign();
        return state[editableDesign][type ?? 'elements']?.[id] ?? {};
    },

    dropPlaceholderClass: (state) => {
        let placeholderClass;
        switch (state.dragSource.group) {
            case 'section':
                placeholderClass = 'dragging';
                break;
            case 'column':
                placeholderClass = 'dragging--column';
                break;
            case 'element':
                placeholderClass = 'dragging--element';
                break;
            default:
                placeholderClass = '';
        }
        return placeholderClass;
    },

    //* Publish URL
    publishURL: (state) => {
        const domain = state.publishDomain;
        if (!domain) return '#';
        return state.isDefaultPage
            ? `https://${domain}`
            : `https://${domain}/${state.builderSettings.path ?? ''}`;
    },

    //* Products
    getSelectedProduct: (_s, _g, rootState) => (refKey) =>
        (rootState.onlineStore?.allProducts ?? []).find(
            (product) => product.reference_key === refKey
        ),

    isBrowserSupportWebp: () =>
        document.documentElement.classList.contains('webp'),

    //* Responsive Image
    responsiveImageUrl:
        (state, getters) =>
        ({ imgUrl, imgContainerWidth }) => {
            if (!getters.isBrowserSupportWebp) return imgUrl;
            const url = imgUrl ?? '';
            const imageExtension = url.lastIndexOf('.');
            const imageUrlWithoutExtension =
                url.substring(0, imageExtension) ?? null;
            if (state.responsiveMode === 'mobile' || imgContainerWidth <= 380) {
                return `${imageUrlWithoutExtension}--small.webp`;
            }
            if (imgContainerWidth <= 760) {
                return `${imageUrlWithoutExtension}--medium.webp`;
            }
            if (imgContainerWidth <= 1140) {
                return `${imageUrlWithoutExtension}--large.webp`;
            }
            return `${imageUrlWithoutExtension}.webp`;
        },

    //* Responsive setting
    settingIndex: (state) => {
        if (state.responsiveMode === 'desktop') return 0;
        if (state.responsiveMode === 'tablet') return 1;
        return 2;
    },

    isShowResetIcon:
        (state, getters) =>
        ({ settingSource, settingName }) => {
            const index = getters.settingIndex;
            if (index === 0) return false;
            const { id, type } = state.onEditElement;
            const settingArray =
                state[getters.pageBuilderEditableDesign()][type][id][
                    settingSource
                ];
            if (Array.isArray(settingName)) {
                return settingName.some(
                    (setting) =>
                        settingArray[setting]?.[index] !==
                        settingArray[setting]?.[index - 1]
                );
            }
            return (
                settingArray[settingName]?.[index] !==
                settingArray[settingName]?.[index - 1]
            );
        },

    //* General
    isLocal: () => import.meta.env.DEV,

    filterElements:
        (state) =>
        ({ type, templateObject }) => {
            const { sections, columns, elements } = JSON.parse(templateObject);
            const headerFooterComponents = [
                'HeaderFooterSiteLogo',
                'HeaderFooterMenuCart',
                'HeaderFooterSearchForm',
                'HeaderFooterNavMenu',
                'HeaderFooterCurrencyDropdown',
            ];

            const ecommerceComponents = ['LandingProducts'];

            const funnelComponents = [
                'LandingOneStepForm',
                'LandingTwoStepForm',
                'LandingOrderSummary',
            ];

            let elementsToRemove = [];
            switch (type) {
                case 'page':
                    elementsToRemove = Object.values(elements ?? {}).filter(
                        (e) => {
                            return [
                                ...headerFooterComponents,
                                ...funnelComponents,
                            ].includes(e.component);
                        }
                    );
                    break;
                case 'header':
                case 'footer':
                    elementsToRemove = Object.values(elements ?? {}).filter(
                        (e) => {
                            return funnelComponents.includes(e.component);
                        }
                    );
                    break;
                default:
                    elementsToRemove = Object.values(elements ?? {}).filter(
                        (e) => {
                            return [
                                ...headerFooterComponents,
                                ...ecommerceComponents,
                            ].includes(e.component);
                        }
                    );
            }

            elementsToRemove = elementsToRemove.map((e) => e.id);

            /* eslint-disable no-restricted-syntax, guard-for-in */
            for (const id in columns) {
                const elementList = columns[id].settings.element_list;
                elementsToRemove.forEach((elemId) => {
                    const index = elementList.findIndex((e) => e === elemId);
                    if (index !== -1) {
                        elementList.splice(index, 1);
                        delete elements[elemId];
                    }
                });
            }
            /* eslint-disable no-restricted-syntax, guard-for-in */

            return JSON.stringify({
                sections,
                columns,
                elements,
            });
        },
};
