export default {
    data() {
        return {
            loading: false,
            badgeName: '',
            badgeDesign: 'rectangular',
            text: 'Sale',
            showTextColorPicker: false,
            showBackgroundColorPicker: false,
            textColor: '#FFFFFF',
            backgroundColor: '#000000',
            fontSize: 12,
            fontFamily: 'font-roboto',
            marginSize: 2,
            position: 'top-right',
            productSelect: 'all',
            productSelections: [
                { name: 'All Products', value: 'all' },
                { name: 'Specific Products', value: 'specific' },
                { name: 'Specific product category', value: 'category' },
            ],
            selectedProduct: [],
            selectedCategory: [],
            searchInput: '',
            nameErr: false,
            textErr: false,
        };
    },

    computed: {},

    methods: {
        save() {
            const pathUrl = !this.badge
                ? '/product/add-product-badge'
                : `/product/edit-product-badge/${this.badge.id}`;
            axios
                .post(pathUrl, {
                    badgeName: this.badgeName,
                    badgeDesign: this.badgeDesign,
                    text: this.text,
                    fontSize: this.fontSize,
                    textColor: this.textColor,
                    fontFamily: this.fontFamily,
                    backgroundColor: this.backgroundColor,
                    position: this.position,
                    productSelect: this.productSelect,
                    selectedProduct: [...this.selectedProduct],
                    selectedCategory: [...this.selectedCategory],
                    marginSize: this.marginSize,
                })
                .then((response) => {
                    // this.$toast.success('Success', response.data.message);
                    this.$inertia.visit('/product/badges');
                })
                .catch((error) => {
                    // this.$toast.error('Error', 'Unexpected Error Occured');
                });
        },
        showBackgroundColor() {
            this.showTextColorPicker = false;
            this.showBackgroundColorPicker = true;
        },
        showTextColor() {
            this.showBackgroundColorPicker = false;
            this.showTextColorPicker = true;
        },
        updateTextColor(color) {
            this.textColor = color;
            if (/^ *$/.test(this.textColor)) this.textColor = '#FFFFFF';
        },
        updateBackgroundColor(color) {
            this.backgroundColor = color;
            if (/^ *$/.test(this.backgroundColor))
                this.backgroundColor = '#000000';
        },
        closeTextColorPicker() {
            this.showTextColorPicker = false;
        },
        closeBackgroundColorPicker() {
            this.showBackgroundColorPicker = false;
        },
        validateFontSize() {
            if (this.fontSize < 5) {
                this.fontSize = 5;
            }
            if (this.fontSize > 35) {
                this.fontSize = 35;
            }
        },
        validateMarginSize() {
            if (this.marginSize < 0) {
                this.marginSize = 0;
            }
        },
        saveProductBadge() {
            this.nameErr = /^ *$/.test(this.badgeName);
            this.textErr = /^ *$/.test(this.text);

            if (!this.nameErr && !this.textErr) {
                this.save();
            } else {
                // this.$toast.error('Error', 'Check the required fields!');
            }
        },
    },
};
