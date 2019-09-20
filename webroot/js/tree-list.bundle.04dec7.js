(window.webpackJsonp=window.webpackJsonp||[]).push([["tree-list"],{22:function(e,t,i){"use strict";i.r(t),t.default={components:{RelationView:()=>Promise.all([i.e("vendors"),i.e("vendors/async/flatpickr"),i.e("relation-view")]).then(i.bind(null,28)),ResourceRelationView:()=>Promise.all([i.e("vendors"),i.e("filter-box-view")]).then(i.bind(null,71)),ChildrenView:()=>Promise.all([i.e("vendors"),i.e("vendors/async/flatpickr"),i.e("children-view")]).then(i.bind(null,72))},props:{tabOpen:{type:Boolean,default:!1},tabOpenAtStart:{type:Boolean,default:!1},isDefaultOpen:{type:Boolean,default:!1}},data(){return{isOpen:this.isDefaultOpen,isLoading:!1,totalObjects:0}},mounted(){this.isOpen=this.tabOpenAtStart},watch:{tabOpen(){this.isOpen=this.tabOpen}},methods:{toggleVisibility(){this.isOpen=!this.isOpen},onToggleLoading(e){this.isLoading=e},onCount(e,t=!1){(0===this.totalObjects||t)&&(this.totalObjects=e)}}}},68:function(e,t,i){"use strict";i.r(t);var n=i(69),s=i.n(n);t.default={props:["value","reset-value"],template:'<textarea @input="handleChange" :value="text"></textarea>',data:()=>({text:"",originalValue:""}),watch:{text(){this.$nextTick(()=>{s()(this.$el)})},value(){this.originalValue=this.value}},mounted(){this.originalValue=this.value,this.text=this.value,this.$nextTick(()=>{s()(this.$el)})},methods:{handleChange(e){this.text=event.target.value,this.$emit("input",this.text)}}}},73:function(e,t,i){"use strict";i.r(t),t.default={name:"tree-list",template:'\n        <div\n            class="tree-list-node"\n            :class="treeListMode">\n\n            <div v-if="!isRoot">\n                <div v-if="multipleChoice"\n                    class="node-element"\n                    :class="{\n                        \'tree-related-object\': isRelated,\n                        \'disabled\': isCurrentObjectInPath,\n                        \'node-folder\': isFolder,\n                    }">\n\n                    <span\n                        @click.prevent.stop="toggle"\n                        class="icon"\n                        :class="nodeIcon"\n                        ></span>\n                    <input\n                        type="checkbox"\n                        :value="item"\n                        v-model="related"\n                    />\n                    <label\n                        @click.prevent.stop="toggle"\n                        :class="isFolder ? \'is-folder\' : \'\'"><: caption :></label>\n                </div>\n                <div v-else class="node-element"\n                    :class="{\n                        \'tree-related-object\': isRelated || stageRelated,\n                        \'was-related-object\': isRelated && !stageRelated,\n                        \'disabled\': isCurrentObjectInPath\n                    }"\n\n                    @click.prevent.stop="select">\n                    <span\n                        @click.prevent.stop="toggle"\n                        class="icon"\n                        :class="nodeIcon"\n                        ></span>\n                    <label><: caption :></label>\n                </div>\n            </div>\n            <div :class="isRoot ? \'\' : \'node-children\'" v-show="open" v-if="isFolder">\n                <tree-list\n                    @add-relation="addRelation"\n                    @remove-relation="removeRelation"\n                    @remove-all-relations="removeAllRelations"\n                    v-for="(child, index) in item.children"\n                    :key="index"\n                    :item="child"\n                    :multiple-choice="multipleChoice"\n                    :related-objects="relatedObjects"\n                    :object-id=objectId>\n                </tree-list>\n            </div>\n        </div>\n    ',data:()=>({stageRelated:!1,related:!1,open:!0}),props:{multipleChoice:{type:Boolean,default:!0},captionField:{type:String,required:!1,default:"name"},childrenField:{type:String,required:!1,default:"children"},item:{type:Object,required:!0,default:()=>{}},relatedObjects:{type:Array,default:()=>[]},objectId:{type:String,required:!1}},computed:{caption(){return this.item[this.captionField]},isFolder(){return this.item.children&&!!this.item.children.length},isRoot(){return this.item.root||!1},isRelated(){return!!this.item.id&&!!this.relatedObjects.filter(e=>e.id===this.item.id).length},isCurrentObjectInPath(){return this.item&&this.item.object&&-1!==this.item.object.meta.path.indexOf(this.objectId)},nodeIcon(){let e="";return e+=this.isFolder?this.open?"icon-down-dir":"icon-right-dir":"unicode-branch"},treeListMode(){let e=[];return this.isRoot&&e.push("root-node"),this.multipleChoice?e.push("tree-list-multiple-choice"):e.push("tree-list-single-choice"),this.isCurrentObject&&e.push("disabled"),e.join(" ")}},watch:{related(e){this.stageRelated=e},stageRelated(e){this.item.object&&(e?this.$emit("add-relation",this.item.object):this.$emit("remove-relation",this.item.object))},relatedObjects(){this.related=this.isRelated}},methods:{toggle(){this.isFolder&&(this.open=!this.open)},addRelation(e){this.$emit("add-relation",e)},removeRelation(e){this.$emit("remove-relation",e)},removeAllRelations(){this.$emit("remove-all-relations")},select(){this.isCurrentObjectInPath||(this.$emit("remove-all-relations"),this.stageRelated=!this.stageRelated)}}}},76:function(e,t,i){"use strict";i.r(t),t.default={props:{jobs:{type:Array,default:()=>[]},services:{type:Array,default:()=>[]},timeout:{type:Number,default:5e3}},data:()=>({fileName:"",currentJobs:()=>[],showPayloadId:null,currentFilterId:null}),created(){this.currentJobs=this.jobs},mounted(){this.services.length&&setInterval(()=>{this.updateJobs()},this.timeout)},methods:{onFileChanged(e){this.fileName=e.target.files[0].name},updateJobs(){let e=`${BEDITA.base}/import/jobs`;return fetch(e,{credentials:"same-origin",headers:{accept:"application/json"}}).then(e=>e.json()).then(e=>{if(e.jobs)return this.currentJobs=e.jobs,this.currentJobs}).catch(e=>{console.error(e)})},togglePayload(e){this.showPayloadId!=e?this.showPayloadId=e:this.showPayloadId=null}}}},82:function(e,t,i){"use strict";i.r(t),t.default={props:{timeout:{type:Number,default:4},isBlocking:{type:Boolean,default:!1},waitPanelAnimation:{type:Number,default:.5}},data:()=>({isVisible:!0,isDumpVisible:!1}),mounted(){this.$nextTick(()=>{this.isBlocking||setTimeout(()=>{this.hide()},1e3*this.timeout)})},methods:{hide(){this.isVisible=!this.isVisible,setTimeout(()=>{this.$refs.flashMessagesContainer.remove()},1e3*this.waitPanelAnimation)}}}},84:function(e,t,i){"use strict";i.r(t);var n=i(66),s=i.n(n);i(67);const l={mode:"code",modes:["tree","code"],history:!0,search:!0};t.default={template:"\n    <div>\n        <slot></slot>\n    </div>\n    ",props:{el:{type:HTMLTextAreaElement}},async mounted(){const e=this.el,t=e.value;try{const i=""!==t&&JSON.parse(t)||{};if(i){e.style.display="none";let t=document.createElement("div");t.className="jsoneditor-container",e.parentElement.insertBefore(t,e),e.dataset.originalValue=e.value;let n=Object.assign(l,{onChange:function(){try{const t=e.jsonEditor.get();e.value=JSON.stringify(t),console.info("valid json :)");let i=e.value!==e.dataset.originalValue;e.dispatchEvent(new CustomEvent("change",{bubbles:!0,detail:{id:e.id,isChanged:i}}))}catch(e){console.warn("still not valid json")}}});e.jsonEditor=new s.a(t,n),e.jsonEditor.set(i)}}catch(e){console.error(e)}}}}}]);