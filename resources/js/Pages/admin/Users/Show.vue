<template>
  <admin-layout title="Profile">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        All Users
      </h2>
    </template>

    <div>
      <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
          <Table
            :filters="queryBuilderProps.filters"
            :search="queryBuilderProps.search"
            :columns="queryBuilderProps.columns"
            :on-update="setQueryBuilder"
            :meta="users"
          >
            <template #head>
              <tr>
                <th @click.prevent="sortBy('name')">Name</th>
                <th
                  v-show="showColumn('email')"
                  @click.prevent="sortBy('email')"
                >
                  Email
                </th>
                <th>Email Verified At</th>
              </tr>
            </template>

            <template #body>
              <tr v-for="user in users.data" :key="user.id">

                <td>{{ user.name }}</td>
                <td v-show="showColumn('email')">{{ user.email }}</td>
                <td>{{ formattedDate(user.email_verified_at) }}</td>
              </tr>
            </template>
          </Table>
      </div>
    </div>
  </admin-layout>
</template>


<script>
import { defineComponent } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import moment from 'moment';
import {
  InteractsWithQueryBuilder,
  Tailwind2,
} from "@protonemedia/inertiajs-tables-laravel-query-builder";

export default defineComponent({
  mixins: [InteractsWithQueryBuilder],

  components: {
    Table: Tailwind2.Table,
    AdminLayout,
  },

  props: {
    users: Object,
  },
  methods:{
      formattedDate(date){
          return date ? moment(date).fromNow() : 'Not Verified';
      }
  }
});
</script>
