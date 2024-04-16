<template>

    <li v-for="(comment, commentIndex) in comments" :key="comment.id" class="mb-3">
        <div class="card">
            <div class="card-header align-items-center justify-content-between d-flex">
                <h5 class="card-title mb-0"><small>{{ comment.id }}. </small><strong>{{ comment.user_name }}</strong>
                </h5>
                <small class="small text-muted">{{ formatDate(comment.created_at) }}</small>
            </div>
            <div class="card-body">
                <div>
                    <p class="card-text" v-html="decodeURIComponent(comment.text)"></p>
                    <div class="image"
                         v-if="comment.file != null && comment.file_type === 1"
                         @click="index = commentIndex"
                         :style="{ backgroundImage: 'url(' + comment.file + ')' }"
                    ></div>
                    <a v-if="comment.file != null && comment.file_type === 2" :href="comment.file" download>TXT file</a>
                </div>
                <button @click="replyToComment(comment)" class="btn btn-sm btn-primary">Reply</button>
                <ul v-if="comment.children && comment.children.length > 0" class="list-unstyled ml-4 mt-3">
                    <!-- Recursively call CommentComponent for nested comments -->
                    <CommentComponent :comments="comment.children" @replyToComment="replyToComment"></CommentComponent>
                </ul>
            </div>
        </div>
        <CoolLightBox v-if="index === commentIndex" :index="index" @close="index = null"></CoolLightBox>
    </li>
</template>

<script>

import CoolLightBox from 'vue-cool-lightbox'
import 'vue-cool-lightbox/dist/vue-cool-lightbox.min.css'

export default {
    emits: ['replyToComment'],
    components: {
        CoolLightBox
    },
    props: {
        comments: {
            type: Array,
            required: true
        }
    },
    methods: {
        replyToComment(comment) {
            this.$emit('replyToComment', comment);
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            const day = ('0' + date.getDate()).slice(-2);
            const month = ('0' + (date.getMonth() + 1)).slice(-2);
            const year = date.getFullYear();
            const hours = ('0' + date.getHours()).slice(-2);
            const minutes = ('0' + date.getMinutes()).slice(-2);
            return `${day}.${month}.${year} ${hours}:${minutes}`;
        }
    },
    data() {
        return {
            index: null
        }
    }
};
</script>
<style>
.comment-image {
    max-width: 70px;
}
</style>
