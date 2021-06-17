<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Accounts Info
      </h2>
      <div class="relative">
        <div class="absolute -top-9 right-0 mx-2">
          <!-- absolute -top-7 right-0 -->
          <inertia-link
            :href="route('ads-accounts.create')"
            class="inline-block bg-indigo-500 hover:bg-indigo-400 focus:outline-none focus:ring focus:ring-offset-2 focus:ring-indigo-500 focus:ring-opacity-50 text-white px-5 py-3 hover:-translate-y-0.5 transform transition active:bg-indigo-600 rounded-lg shadow-lg uppercase tracking-wider font-semibold text-sm"
          >
            Create New Account
          </inertia-link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <table class="table-auto mx-auto">
              <thead>
                <tr>
                  <th class="px-2">Id</th>
                  <th class="px-2">アカウント名</th>
                  <th class="px-2">Aql アカウント名</th>
                  <th class="px-2">プラットホーム</th>
                  <th class="px-2">月々の予算</th>
                  <th class="px-2">一日あたりの予算</th>
                  <th class="px-2">編集と削除</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(data, index) in accounts"
                  :key="index"
                  :class="[
                    index % 2 != 0
                      ? 'bg-green-100 hover:bg-gray-200'
                      : 'hover:bg-gray-200',
                  ]"
                >
                  <td class="px-2">{{ data.accountId }}</td>
                  <td class="px-2">{{ data.name }}</td>
                  <td class="px-2">{{ data.aqlName }}</td>
                  <td class="px-2">{{ data.platform }}</td>
                  <td class="px-2">
                    {{
                      data.monthlyBudget == 2147483647
                        ? "Unlimited"
                        : formatPrice(data.monthlyBudget)
                    }}
                  </td>
                  <td class="px-2">{{ formatPrice(data.dailyBudget) }}</td>
                  <td class="px-2 flex flex-row space-x-2">
                    <inertia-link
                      :href="route('ads-accounts.edit', data.id)"
                      class="text-blue-600"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-7 w-7"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"
                        />
                      </svg>
                    </inertia-link>
                    <button @click="destroy(data.id)" class="text-red-600">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-7 w-7"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'
export default {
  props: ["accounts", "success",],
  components: {
    AppLayout,
  },
  methods: {
    formatPrice(value) {
      const formatter = new Intl.NumberFormat("en-US", { style: "currency", currency: "JPY",
        maximumFractionDigits: 0,
      });
      return formatter.format(value);
    },
    destroy(id) {
      if (confirm("Are you sure you want to delete this Account?")) {
        this.$inertia.delete(this.route("ads-accounts.destroy", id));
      }
    },
  },
  mounted() {
    if (this.$page.props.flash.success) {
      this.$toast.success(this.$page.props.flash.success);
    } else if (this.$page.props.flash.error) {
      this.$toast.error(this.$page.props.flash.error);
    }
  },
};
</script>

<style>
</style>