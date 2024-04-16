import { defineComponent, createApp } from 'vue';
import CommentsComponent from "./components/CommentsComponent.vue";
const app = createApp(defineComponent({
    components: {
        CommentsComponent
    },
    template: `
        <div>
            <CommentsComponent />
        </div>
    `
}));

app.mount("#app")
