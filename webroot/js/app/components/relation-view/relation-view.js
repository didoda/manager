/**
 * Templates that uses this component (directly or indirectly):
 *  Template/Elements/relations.twig
 *
 * <relation-view> component used for ModulesPage -> View
 *
 */

Vue.component('relation-view', {
    mixins: [ PaginatedContentMixin ],

    props: ['relationName', 'loadOnStart'],

    data() {
        return {
            method: 'relatedJson',          // define AppController method to be used
            loading: false,
            showRelationshipsPanel: false,

            removedRelated: [],             // currently related objects to be removed
            addedRelations: [],             // staged added objects to be saved
            hideRelations: [],              // hide already added relations in relationships-view
            relationsData: [],              // hidden field containing serialized json passed on form submit

            step: DEFAULT_PAGINATION.page_size,     // step value for pagination page size

            pageSizeOptions: [
                20,
                50,
                100,
            ]
        }
    },

    /**
     * setup correct endpoint for PaginatedContentMixin.getPaginatedObjects()
     *
     * @return {void}
     */
    created() {
        this.endpoint = `${this.method}/${this.relationName}`;

        if (this.loadOnStart) {
            this.loadRelatedObjects();
        }
    },

    watch: {
        /**
         * watcher for step variable, change pageSize and reload relations
         *
         * @param {Number} value
         */
        step(value) {
            this.setPageSize(value);
            this.loadRelatedObjects();
        },
    },

    methods: {
        /**
         * call PaginatedContentMixin.getPaginatedObjects() method and handle loading
         *
         * @return {Boolean} response;
         */
        async loadRelatedObjects() {
            this.loading = true;

            let resp = await this.getPaginatedObjects();
            this.loading = false;

            return resp;
        },

        /**
         * remove related object: adding it to removedRelated Array
         *
         * @param {Number} id
         * @param {String} type
         *
         * @returns {void}
         */
        removeRelations(id, type) {
            if (!this.containsId(this.removedRelated, id)) {
                this.removedRelated.push({
                    id,
                    type,
                });

                this.relationsData = this.relationFormatterHelper(this.removedRelated);
            }
        },

        /**
         * re-add removed related object: removing it from removedRelated Array
         *
         * @param {Number} id
         * @param {String} type
         *
         * @returns {void}
         */
        reAddRelations(id, type) {
            this.removedRelated = this.removedRelated.filter((rel) => rel.id !== id);

            this.relationsData = this.relationFormatterHelper(this.removedRelated);
        },

        /**
         * show next page of paginated content
         *
         */
        async showMoreRelated() {
            this.loading = true;
            await this.loadMore(this.step);
            this.loading = false;
        },

        /**
         * load first page of content returns newly loaded objects
         *
         * @param {Boolean} autoload if false it doesn't update this.objects [DEFAULT = true]
         *
         * @return {Promise} repsonse from server with new data
         */
        async firstPage(autoload = true) {
            this.loading = true;

            // calling Mixin's method
            let resp =  await PaginatedContentMixin.methods.firstPage.call(this, autoload);
            this.loading = false;

            return resp;
        },

        /**
         * load last page of content returns newly loaded objects
         *
         * @param {Boolean} autoload if false it doesn't update this.objects [DEFAULT = true]
         *
         * @return {Promise} repsonse from server with new data
         */
        async lastPage(autoload = true) {
            this.loading = true;

            // calling Mixin's method
            let resp =  await PaginatedContentMixin.methods.lastPage.call(this, autoload);
            this.loading = false;

            return resp;
        },

        /**
         * load next page of content returns newly loaded objects
         *
         * @param {Boolean} autoload if false it doesn't update this.objects [DEFAULT = true]
         *
         * @return {Promise} repsonse from server with new data
         */
        async nextPage(autoload = true) {
            this.loading = true;

            // calling Mixin's method
            let resp =  await PaginatedContentMixin.methods.nextPage.call(this, autoload);
            this.loading = false;

            return resp;
        },

        /**
         * load prev page of content returns newly loaded objects
         *
         * @param {Boolean} autoload if false it doesn't update this.objects [DEFAULT = true]
         *
         * @return {Promise} repsonse from server with new data
         */
        async prevPage(autoload = true) {
            this.loading = true;

            // calling Mixin's method
            let resp =  await PaginatedContentMixin.methods.prevPage.call(this, autoload);
            this.loading = false;

            return resp;
        },

        /**
         * remove element with matched id from staged relations
         *
         * @param {Number} id
         */
        removeAddedRelations(id) {
            this.addedRelations = this.addedRelations.filter((rel) => rel.id !== id);
        },

        /**
         * Show relationships-view and pass objects already related which need to be hidden
         *
         * @return {void}
         */
        showRelationshipsModal() {
            // this.hideRelations is passed as prop to relationships-view
            this.hideRelations = this.objects;
            this.showRelationshipsPanel = true;
        },

        /**
         * helper function for template
         *
         * @return {Boolean} true if has at least a related object or a newly added object
         */
        hasElementsToShow() {
            return (this.objects && this.objects.length) || (this.addedRelations && this.addedRelations.length);;
        },

        /**
         * Event 'added-relations' callback
         * retrieve last added relations from relationships-view
         *
         * @param {Array} relations
         *
         * @return {void}
         */
        appendRelations(relations) {
            this.addedRelations = relations;
        },

        /**
         * Event 'visibility-setter' callback
         * set current view's relationships-view visibility from child view
         *
         * @param {Boolean} isVisible
         *
         * @return {void}
         */
        setRelationshipPanelVisibility(isVisible) {
            this.showRelationshipsPanel = isVisible;
        },

        /**
         * helper function: check if array relations has element with id -> id
         *
         * @param {Array} relations
         * @param {Number} id
         *
         * @return {Boolean} true if id is in Array relations
         */
        containsId(relations, id) {
            return relations.filter((rel) => rel.id === id).length;
        },

        /**
         * helper function: convert array to string
         *
         * @param {Array} relations
         *
         * @return {String} string version of relations
         */
        relationFormatterHelper(relations) {
            return JSON.stringify(relations);
        },

        /**
         * helper function: build open view url
         *
         * @param {String} objectType
         * @param {Number} objectId
         *
         * @return {String} url
         */
        buildViewUrl(objectType, objectId) {
            return `${window.location.protocol}//${window.location.host}/${objectType}/view/${objectId}`;
        },
    }

});