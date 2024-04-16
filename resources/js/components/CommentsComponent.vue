<template>
    <div class="container d-flex justify-content-between gap-5 ">
        <div class="w-25">
            <h2>Add Comment</h2>
            <form-component @addComment="addComment" @dropParentId="dropParentId"
                            :reply_to="parent_id"></form-component>
        </div>
        <div class="w-75">
            <div class="card">
                <div class="card-header  d-flex justify-content-between align-items-center">
                    <h2>Comments</h2>
                    <sort-component :current_page="current_page" @getComments="getComments"></sort-component>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <comment-component @replyToComment="replyToComment" :comments="comments"></comment-component>
                    </ul>
                </div>
            </div>
            <div class="container mt-5 mb-5" v-if="pages > 1">
                <PaginationComponent @getComments="getComments" :total_items="total_items" :current_page="current_page"
                                     :pages="pages" :per_page="per_page"></PaginationComponent>
            </div>
        </div>
    </div>

</template>


<script>
import axios from "axios";
import CommentComponent from "./CommentComponent.vue";
import FormComponent from "./FormComponent.vue";
import PaginationComponent from "./PaginationComponent.vue";
import SortComponent from "./SortComponent.vue";
import Pusher from "pusher-js";
import Echo from 'laravel-echo'

export default {
    components: {CommentComponent, FormComponent, PaginationComponent, SortComponent},
    data() {
        return {
            parent_id: null,
            comments: [],
            pages: 0,
            current_page: 0,
            total_items: 0,
            per_page: 0,
            echo: new Echo({
                broadcaster: "pusher",
                key: 'c10927c5e6bfd95c78c1',
                cluster: 'eu',
                forceTLS: true
            })
        };
    },
    methods: {
        replyToComment(comment) {
            this.parent_id = comment.id
        },
        getComments(page = 1, sortBy = 'created_at', sortOrder = 'desc') {
            const query = `
                query GetComments($sortBy: String, $sortOrder: String, $page: Int) {
                    comments(sortBy: $sortBy, sortOrder: $sortOrder, page: $page) {
                        data {
                            id
                            user_name
                            email
                            home_page
                            file
                            file_type
                            text
                            parent_id
                            created_at
                            updated_at
                        }
                        paginatorInfo {
                            currentPage
                            lastPage
                            total
                            perPage
                        }
                    }
                }
            `;

            const variables = {
                sortBy: sortBy, // Установите значение по умолчанию, если нужно
                sortOrder: sortOrder, // Установите значение по умолчанию, если нужно
                page: page // Установите значение по умолчанию, если нужно
            };

            console.log()


            axios.post(`/graphql`, {
                query,
                variables
            })
                .then(response => {
                    this.comments = this.organizeComments(response.data.data.comments.data);
                    this.pages = response.data.data.comments.paginatorInfo.lastPage;
                    this.current_page = response.data.data.comments.paginatorInfo.currentPage;
                    this.total_items = response.data.data.comments.paginatorInfo.total
                    this.per_page = response.data.data.comments.paginatorInfo.perPage
                })
        },
        organizeComments(comments, parentId = null) {
            const result = [];

            comments.forEach(comment => {
                if (comment.parent_id === parentId) {
                    const children = this.organizeComments(comments, comment.id);
                    if (children.length) {
                        comment.children = children;
                    }
                    result.push(comment);
                }
            });

            return result;
        },

        addCommentRecursive(commentList, parentId, newComment) {
            commentList.forEach(comment => {
                if (comment.id === parentId) {
                    if (!comment.children) {
                        comment.children = [];
                    }
                    comment.children.push(newComment);
                } else if (comment.children) {
                    this.addCommentRecursive(comment.children, parentId, newComment);
                }
            });
        },
        addComment(form_data) {
            // Отправка данных о новом комментарии на сервер
            const parent_id = this.parent_id

            if (parent_id !== null) {
                form_data.append('parent_id', parent_id);
            }

            axios.post('/api/make-comment', form_data)
                .then(response => {
                    // Если есть parent_id, добавляем новый комментарий как дочерний элемент
                    if (parent_id !== null) {
                        this.addCommentRecursive(this.comments, parent_id, response.data.data);
                    } else {
                        // Если parent_id равен null, добавляем новый комментарий в корень
                        this.comments.unshift(response.data.data);
                    }
                    // Сбрасываем parent_id
                    this.parent_id = null;
                })
                .catch(error => {
                    console.error(error);
                });
        },
        dropParentId() {
            this.parent_id = null;
        },
        openSocket() {
            var channel = this.echo.channel('mutual-house-513');
            channel.listen('.new-comment', (data) => {
                const message = data.message;
                if (message.parent_id === null) {
                    this.comments.unshift(message);
                } else {
                    this.addCommentRecursive(this.comments, message.parent_id, message);
                }
            });

        }
    },
    mounted() {
        this.getComments()
        this.openSocket()
    }
}
</script>
<script setup>
</script>
