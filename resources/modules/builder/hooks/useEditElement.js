import { computed, toRefs, reactive, watch } from 'vue';
import { useStore } from 'vuex';

export default function useEditElement(settingType = 'settings') {
    const store = useStore();
    const element = reactive({
        id: null,
        name: null,
        type: null,
        settings: null,
        styles: null,
        advanced: null,
    });

    const onEditElementAttributes = computed(
        () => store.getters['builder/onEditElementAttributes']
    );

    const themeStyles = computed(() => store.state.builder.themeStyles);
    const settingIndex = computed(() => store.getters['builder/settingIndex']);

    watch(
        onEditElementAttributes,
        (newValue) => {
            if (!newValue) return;
            const { id, name, type, settings, styles, advanced } = newValue;
            element.id = id;
            element.name = name;
            element.type = type;
            element.settings = settings;
            element.styles = styles;
            element.advanced = advanced;
        },
        { immediate: true }
    );

    const commitUpdateElement = (
        setting,
        value,
        index = null,
        category = 'elements'
    ) => {
        store.commit('builder/updateElement', {
            setting,
            value,
            index,
            settingType,
            responsiveSettingIndex: settingIndex.value,
            category, //* sections, columns, elements, (h1, h2, ...), (primaryButton, ...), product
        });
        if (Array.isArray(setting)) {
            const settingName = (setting[0] ?? '').toLowerCase();
            if (
                settingName.includes('fontfamily') ||
                settingName === 'selectedfont'
            ) {
                store.commit('builder/updatePageFonts', {
                    setting: setting[0],
                    value,
                });
            }
        }
    };

    return {
        ...toRefs(element),
        element: computed(() => onEditElementAttributes.value).value,
        themeStyles,
        settingIndex,
        commitUpdateElement,
    };
}
