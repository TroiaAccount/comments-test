<template>
    <div class="d-flex">
        <select class="form-select" v-model="sortBy">
            <option value="user_name">User Name</option>
            <option value="email">E-mail</option>
            <option value="created_at">Date Added</option>
        </select>

        <select class="form-select" v-model="sortOrder">
            <option value="asc">Ascending</option>
            <option value="desc">Descending</option>
        </select>

        <button class="btn btn-primary" @click="getComments(current_page, sortBy, sortOrder)">Sort</button>
    </div>
</template>

<script>
export default {
    emits: ['getComments'],
    data() {
        return {
            sortBy: "created_at",
            sortOrder: 'desc'
        }
    },
    props: {
        current_page: {
            required: true,
            type: Number
        }
    },
    methods: {
        getComments(page, sortBy, sortOrder) {
            this.$emit('getComments', page, sortBy, sortOrder)
        }
    }
}
</script>
