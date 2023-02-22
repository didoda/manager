import { t } from 'ttag';

export default {
    name: 'index-cell',

    template: `
    <div :class="className()" untitled-label="${t`Untitled`}" @mouseover="onMouseover()" @mouseleave="onMouseleave()">
        <: !msg ? truncated : '' :>
        <icon-copy v-if="showCopyIcon()" @click.stop.prevent="copy()"></icon-copy>
        <div v-if="msg" v-text="msg" style="color: gray"></div>
    </div>
    `,

    components: {
        // icons
        IconCopy: () => import(/* webpackChunkName: "icon-copy" */'@carbon/icons-vue/es/copy/16.js'),
    },

    props: {
        settings: {},
        prop: '',
        text: '',
        untitledlabel: '',
    },

    data() {
        return {
            msg: '',
            showCopy: false,
            truncated: '',
        };
    },

    async mounted() {
        this.truncated = this.text.length <= 100 ? this.text : this.text.substring(0, 100);
    },

    methods: {
        className() {
            return `${this.prop}-cell`;
        },

        copy() {
            navigator.clipboard.writeText(this.text);
            this.msg = t`copied in the clipboard`;
            setTimeout(() => this.reset(), 2000)
        },

        onMouseover() {
            if (this.settings?.copy2clipboard !== true) {
                return;
            }
            this.showCopy = true;
        },

        onMouseleave() {
            if (this.settings?.copy2clipboard !== true) {
                return;
            }
            this.showCopy = false;
        },

        reset() {
            this.msg = '';
        },

        showCopyIcon() {
            if (this.settings?.copy2clipboard !== true) {
                return false;
            }

            return !this.msg && this.showCopy;
        },
    },
};
