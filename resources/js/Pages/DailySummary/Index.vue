<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        SSS Hourly Report
      </h2>
    </template>
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div
          class="pt-2 sm:px-15 bg-white overflow-hidden shadow-sm sm:rounded-lg"
        >
          <div
            class="w-full h-screen py-6 px-2 bg-white border-b border-gray-200"
          >
            <table class="table-auto mx-auto">
              <thead>
                <tr>
                  <th
                    v-for="(header, index) in table_header"
                    :key="index"
                    class="
                      border
                      mx-2
                      px-2
                      border-r-1 border-red-100
                      bg-green-600
                      text-white
                    "
                  >
                    <span class="font-light" v-html="header"></span>
                  </th>
                  <th
                    class="
                      border
                      mx-2
                      px-2
                      border-r-1 border-red-100
                      bg-green-600
                      text-white
                      font-light
                    "
                  >
                    編集
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(account, index) in accountList"
                  :key="index"
                  class="hover:bg-gray-200"
                  :class="[index % 2 != 0 ? 'bg-green-100' : '']"
                >
                  <td class="px-1">
                    <span class="font-light" v-html="account.date"></span>
                  </td>
                  <td class="px-1">
                    <span
                      class="font-light"
                      v-html="account.day"
                    ></span>
                  </td>
                  <td class="px-1">
                    <span
                      class="font-light"
                      v-html="account.googleAds"
                    ></span>
                  </td>
                  <td class="px-1">
                    <span
                      class="font-light"
                      v-html="account.yahooAds"
                    ></span>
                  </td>
                  <td class="px-1">
                    <span
                      class="font-light"
                      v-html="account.total"
                    ></span>
                  </td>
                  <td class="px-1">
                    <span
                      class="font-light"
                      v-html="account.numberOfCalls"
                    ></span>
                  </td>
                  <td class="px-1">
                    <span
                      class="font-light"
                      v-html="account.costPerCall"
                    ></span>
                  </td>
                  <td class="px-1" v-if="account.id">
                    <inertia-link
                      :href="route('daily-summary.edit', account.id)"
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
import AppLayout from "@/Layouts/AppLayout";
import SideBar from "@/Components/Sidebar";
import { ref, computed,reactive, onMounted } from "vue";
export default {
  components: {
    AppLayout,
    SideBar,
  },
  props: {
    dailyDatas: Object,
  },
  setup(props) {
    const table_header = ref([ "日付", "day", "google", "yahoo", "予算", "入電数", "入電単価"]);
    const accountList = reactive([]);
    const formatPrice = (value)=> {
      const formatter = new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "JPY",
        maximumFractionDigits: 0,
      });
      return formatter.format(value);
    };
    const getDayOfWeek =(date)=> {
      const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
      const dt = new Date(date);
      return days[dt.getDay()];
    };
    const getaccounts = () => {
      for (const [key, value] of Object.entries(props.dailyDatas.data)) {
        const dailysummary = {
          "id": value.id,
          "date": value.date,
          "day": getDayOfWeek(value.date),
          "accountName": value.accountName,
          "yahooAds": formatPrice(value.yahooAds),
          "googleAds": formatPrice(value.googleAds),
          "total": formatPrice(value.total),
          "budget": formatPrice(value.budget),
          "numberOfCalls": value.numberOfCalls,
          "costPerCall": formatPrice(value.costPerCall),
        };
        accountList.push(dailysummary);
      }
    };
    onMounted(getaccounts);

    return { table_header, accountList };
  },
};
</script>
