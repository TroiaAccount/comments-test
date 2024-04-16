<template>
    <div class="pagination w-100 d-flex justify-content-center gap-2">
        <vue-awesome-paginate
            :total-items="total_items"
            :items-per-page="per_page"
            :max-pages-shown="5"
            v-model="current"
            @click="getComments"
            :show-breakpoint-buttons="true"
        ></vue-awesome-paginate>
    </div>
</template>

<script>
import {VueAwesomePaginate} from "vue-awesome-paginate";

export default {
    emits: ['getComments'],
    components: {VueAwesomePaginate},
    data() {
        return {
            current: 1
        }
    },
    props: {
        current_page: {
            required: true,
            type: Number
        },
        pages: {
            required: true,
            type: Number
        },
        total_items: {
            required: true,
            type: Number
        },
        per_page: {
            required: true,
            type: Number
        }
    },
    methods: {
        getComments(page) {
            this.$emit("getComments", page);
        }
    },
    watch: {
        current_page(newVal) {
            this.current = newVal
        }
    }
};
</script>

<style>
.pagination-container {
    display: flex;
    column-gap: 10px;
}
.paginate-buttons {
    height: 40px;
    width: 40px;
    border-radius: 20px;
    cursor: pointer;
    background-color: rgb(242, 242, 242);
    border: 1px solid rgb(217, 217, 217);
    color: black;
}
.paginate-buttons:hover {
    background-color: #d8d8d8;
}
.active-page {
    background-color: #3498db;
    border: 1px solid #3498db;
    color: white;
}
.active-page:hover {
    background-color: #2988c8;
}
</style>
