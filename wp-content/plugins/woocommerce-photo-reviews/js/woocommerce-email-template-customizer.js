jQuery(document).ready(function ($) {
    'use strict';
    const i18n = viWecParams.i18n;
    ViWec.Components.registerCategory('woocommerce_photo_reviews_review_reminder', 'WooCommerce Photo Reviews');
    let woocommerce_photo_reviews_review_reminder = viwec_woocommerce_photo_reviews.review_reminder;
    woocommerce_photo_reviews_review_reminder.properties = [
        {
            key: "table_style",
            inputType: SectionInput,
            name: false,
            target: '.viwec-item-row',
            section: styleSection,
            data: {header: "Product item"}
        },
        {
            name: "Background color",
            key: "background-color",
            target: '.viwec-item-row',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            inputType: ColorInput
        },
        {
            name: "Image size",
            key: "width",
            target: '.viwec-product-img',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            unit: 'px',
            inputType: NumberInput
        },
        {
            name: "Border width(px)",
            key: "border-width",
            target: '.viwec-item-row',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            unit: 'px',
            inputType: NumberInput,
            data: {value: 20},
        },
        {
            name: "Border color",
            key: "border-color",
            target: '.viwec-item-row',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            inputType: ColorInput,
            data: {value: 20},
        },
        {
            name: "Items distance(px)",
            key: "padding-top",
            target: '.viwec-product-distance',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            unit: 'px',
            inputType: NumberInput,
        },
        {
            name: "Alignment",
            key: "text-align",
            htmlAttr: "childStyle",
            target: '.viwec-product-detail',
            validValues: ["", "text-left", "text-center", "text-right"],
            section: styleSection,
            col: 8,
            inline: true,
            inputType: RadioButtonInput,
            data: {extraClass: "left", options: viWecAlignmentOptions},
        },
        {
            key: "product_name",
            inputType: SectionInput,
            name: false,
            target: '.viwec-product-name',
            section: styleSection,
            data: {header: "Product title"}
        },
        {
            name: "Font size(px)",
            key: "font-size",
            target: '.viwec-product-name',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            unit: 'px',
            inputType: NumberInput
        },
        {
            name: "Font weight",
            key: "font-weight",
            target: '.viwec-product-name',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            inputType: SelectInput,
            data: {
                options: viWecFontWeightOptions
            }
        },
        {
            name: "Color",
            key: "color",
            target: '.viwec-product-name',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            inputType: ColorInput
        },
        {
            name: "Line height(px)",
            key: "line-height",
            target: '.viwec-product-name',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            unit: 'px',
            inline: true,
            inputType: NumberInput
        },
        {
            key: "product_price",
            inputType: SectionInput,
            name: false,
            target: '.viwec-product-price',
            section: styleSection,
            data: {header: "Product price"}
        },
        {
            name: "Font size(px)",
            key: "font-size",
            target: '.viwec-product-price',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            unit: 'px',
            inputType: NumberInput
        },
        {
            name: "Font weight",
            key: "font-weight",
            target: '.viwec-product-price',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            inputType: SelectInput,
            data: {
                options: viWecFontWeightOptions
            }
        },
        {
            name: "Color",
            key: "color",
            target: '.viwec-product-price',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            inputType: ColorInput
        },
        {
            name: "Line height(px)",
            key: "line-height",
            target: '.viwec-product-price',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            unit: 'px',
            inline: true,
            inputType: NumberInput
        },
        {
            key: "review_button",
            inputType: SectionInput,
            name: false,
            target: '.viwec-product-review-button',
            section: styleSection,
            data: {header: "Review Button"}
        },
        {
            name: "Button title",
            key: "review_button_title",
            target: '.viwec-product-review-button',
            htmlAttr: "innerHTML",
            section: styleSection,
            col: 16,
            inputType: TextInput
        },
        {
            groupName: "Padding(px)",
            name: "Left",
            key: "padding-left",
            htmlAttr: "childStyle",
            target: '.viwec-product-review-button',
            section: styleSection,
            col: 4,
            unit: 'px',
            inputType: NumberInput,
            data: {value: 20},
        }, {
            name: "Top",
            key: "padding-top",
            htmlAttr: "childStyle",
            target: '.viwec-product-review-button',
            section: styleSection,
            col: 4,
            unit: 'px',
            inputType: NumberInput,
            data: {value: 20},
        }, {
            name: "Right",
            key: "padding-right",
            htmlAttr: "childStyle",
            target: '.viwec-product-review-button',
            section: styleSection,
            col: 4,
            unit: 'px',
            inputType: NumberInput,
            data: {value: 20},
        }, {
            name: "Bottom",
            key: "padding-bottom",
            htmlAttr: "childStyle",
            target: '.viwec-product-review-button',
            section: styleSection,
            col: 4,
            unit: 'px',
            inputType: NumberInput,
            data: {value: 20},
        },
        {
            name: "Border radius(px)",
            key: "border-radius",
            target: '.viwec-product-review-button',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            unit: 'px',
            inline: true,
            inputType: NumberInput
        },
        {
            name: "Line height(px)",
            key: "line-height",
            target: '.viwec-product-review-button',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            unit: 'px',
            inline: true,
            inputType: NumberInput
        },
        {
            name: "Font size(px)",
            key: "font-size",
            target: '.viwec-product-review-button',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            unit: 'px',
            inputType: NumberInput
        },
        {
            name: "Font weight",
            key: "font-weight",
            target: '.viwec-product-review-button',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            inputType: SelectInput,
            data: {
                options: viWecFontWeightOptions
            }
        },
        {
            name: "Color",
            key: "color",
            target: '.viwec-product-review-button',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            inputType: ColorInput
        },
        {
            name: "Background color",
            key: "background-color",
            target: '.viwec-product-review-button',
            htmlAttr: "childStyle",
            section: styleSection,
            col: 8,
            inline: true,
            inputType: ColorInput
        },
    ];
    ViWec.Components.add(woocommerce_photo_reviews_review_reminder);
});
