<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Daily Summary Report
      </h2>
    </template>
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div
          class="pt-2 sm:px-15 bg-white overflow-hidden shadow-sm sm:rounded-lg"
        >
          <div class="flex w-full h-12 space-x-2 justify-end pr-4">
            <div class="mt-1">
              <input
                class="border border-grey-300 rounded-lg shadow-sm mr-2"
                type="date"
                v-model="startDate"
                name="startDate"
                id="startDate"
              />
              <input
                class="border border-grey-300 rounded-lg shadow-sm"
                type="date"
                v-model="endDate"
                name="endDate"
                id="endDate"
              />
            </div>
            <inertia-link
              :href="
                route('daily-summary.index', {
                  startDate: startDate,
                  endDate: endDate,
                })
              "
              class="
                inline-block
                bg-blue-500
                hover:bg-blue-400
                focus:outline-none
                focus:ring
                focus:ring-offset-2
                focus:ring-blue-500
                focus:ring-opacity-50
                text-white
                px-5
                py-3
                hover:-translate-y-0.5
                transform
                transition
                active:bg-blue-600
                rounded-lg
                shadow-lg
                uppercase
                tracking-wider
                font-semibold
                text-sm
              "
            >
              Fetch
            </inertia-link>
          </div>
          <div class="flex">
            <side-bar
              :accounts="accounts"
              :selectedAccount="selectedAccount"
              @toggleAcoount="toggleAcoount"
            />
            <div
              class="
                w-full
                h-screen
                py-6
                px-2
                bg-white
                border-b border-gray-200
              "
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
                    v-for="(account, index) in displayedDatas"
                    :key="index"
                    class="hover:bg-gray-200"
                    :class="[index % 2 != 0 ? 'bg-green-100' : '']"
                  >
                    <td class="px-1">
                      <span class="font-light" v-html="account.date"></span>
                    </td>
                    <td class="px-1">
                      <span class="font-light" v-html="account.day"></span>
                    </td>
                    <td class="px-1">
                      <span
                        class="font-light"
                        v-html="account.googleAds"
                      ></span>
                    </td>
                    <td class="px-1">
                      <span class="font-light" v-html="account.yahooAds"></span>
                    </td>
                    <td class="px-1">
                      <span class="font-light" v-html="account.total"></span>
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
              <ul
                v-if="pages.length > 1"
                class="flex pl-0 list-none rounded my-2"
              >
                <li
                  v-if="page != 1"
                  @click="page--"
                  class="
                    relative
                    block
                    py-2
                    px-3
                    leading-tight
                    bg-white
                    border border-gray-300
                    text-blue-700
                    border-r-0
                    ml-0
                    rounded-l
                    hover:bg-gray-200
                  "
                >
                  <a class="page-link" href="#">Previous</a>
                </li>
                <li
                  :key="pageNumber"
                  v-for="pageNumber in pages.slice(page - 1, page + 5)"
                  @click="page = pageNumber"
                  class="
                    relative
                    block
                    py-2
                    px-3
                    leading-tight
                    bg-white
                    border border-gray-300
                    text-blue-700
                    border-r
                    hover:bg-gray-200
                  "
                >
                  <a class="page-link" href="#">{{ pageNumber }}</a>
                </li>
                <li
                  @click="page++"
                  v-if="page < pages.length"
                  class="
                    relative
                    block
                    py-2
                    px-3
                    leading-tight
                    bg-white
                    border border-gray-300
                    text-blue-700
                    rounded-r
                    hover:bg-gray-200
                  "
                >
                  <a class="page-link" href="#">Next</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import SideBar from "@/Components/Sidebar";

export default {
  components: {
    AppLayout,
    SideBar,
  },
  props: {
    accounts: Object,
    dailyDatas: Object,
  },
  data: () => ({
    table_header: [
      "日付",
      "day",
      "google",
      "yahoo",
      "予算",
      "入電数",
      "入電単価",
    ],
    selectedAccount: "全部",
    accountList: [],
    formatedListForCsv: [],
    startDate: "2021-05-01",
    endDate: "2021-05-07",
    page: 1,
    sortColumn: "",
    sortOrder: 0,
    perPage: 16,
    pages: [],
  }),
  methods: {
    formatPrice(value) {
      const formatter = new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "JPY",
        maximumFractionDigits: 0,
      });
      return formatter.format(value);
    },
    getDayOfWeek(date) {
      const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
      const dt = new Date(date);
      return days[dt.getDay()];
    },
    toggleAcoount(name) {
      this.selectedAccount = name;
    },
    setPages() {
      let numberOfPages = Math.ceil(this.accountList.length / this.perPage);
      if (this.pages.length == numberOfPages) {
        return 0;
      }
      this.pages=[];
      for (let index = 1; index <= numberOfPages; index++) {
        this.pages.push(index);
      }
    },
    paginate(accountList) {
      let page = this.page;
      let perPage = this.perPage;
      let from = page * perPage - perPage;
      let to = page * perPage;
      return accountList.slice(from, to);
    },
  },
  computed: {
    displayedDatas() {
      let accs = [];
      const selectedData = this.dailyDatas[this.selectedAccount];
      for (const [key, value] of Object.entries(selectedData)) {
        const dailysummary = {
          id: value.id,
          date: value.date,
          day: this.getDayOfWeek(value.date),
          accountName: value.accountName,
          yahooAds: this.formatPrice(value.yahooAds),
          googleAds: this.formatPrice(value.googleAds),
          total: this.formatPrice(value.total),
          budget: this.formatPrice(value.budget),
          numberOfCalls: value.numberOfCalls,
          costPerCall: this.formatPrice(value.costPerCall),
        };
        accs.push(dailysummary);
      }
      this.accountList = accs;
      let dailyList = accs;
      if (this.sortColumn) {
        if (this.sortOrder > 0) {
          dailyList = dailyList.sort((a, b) =>
            a[this.sortColumn] > b[this.sortColumn] ? 1 : -1
          );
        } else {
          dailyList = dailyList.sort((a, b) =>
            a[this.sortColumn] < b[this.sortColumn] ? 1 : -1
          );
        }
      }
      return this.paginate(dailyList);
    },
  },
  watch: {
    accountList() {
      this.setPages();
    },
  },
  mounted() {
    const { startDate, endDate } = route().params;
    const selectedData = this.dailyDatas[this.selectedAccount];
    if (startDate && endDate) {
      this.startDate = startDate;
      this.endDate = endDate;
    } else {
      const dates = Object.keys(selectedData);
      this.startDate = dates[0];
      this.endDate = dates[dates.length - 1];
    }
  },
};
</script>
