<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Aql Data
      </h2>
    </template>
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div
          class="pt-2 sm:px-15 bg-white overflow-hidden shadow-sm sm:rounded-lg"
        >
          <div class="flex w-full h-12 space-x-2 justify-end pr-3">
            <!-- <JsonCSV :data="json_data"> Download Data </JsonCSV> -->
            <button
              class="
                inline-block
                bg-green-500
                hover:bg-green-400
                focus:outline-none
                focus:ring
                focus:ring-offset-2
                focus:ring-green-500
                focus:ring-opacity-50
                text-white
                px-5
                py-3
                hover:-translate-y-0.5
                transform
                transition
                active:bg-green-600
                rounded-lg
                shadow-lg
                uppercase
                tracking-wider
                font-semibold
                text-sm
              "
              @click="csvExport(accountList)"
            >
              Download CSV
            </button>
            <!-- <date-picker></date-picker> -->
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
                route('aql-daily-summary', {
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
            <div class="w-full h-screen p-6 bg-white border-b border-gray-200">
              <table class="table-auto mx-auto">
                <thead>
                  <tr v-if="selectedAccount == '全部'">
                    <th
                      v-for="(header, index) in table_header"
                      :key="index"
                      class="px-2 bg-green-600 text-white"
                    >
                      {{ header }}
                    </th>
                  </tr>
                  <tr v-else>
                    <th
                      v-for="(header, index) in accountHeader"
                      :key="index"
                      class="px-2 bg-green-600 text-white"
                    >
                      <span class="text-sm font-light" v-html="header"></span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <template v-if="selectedAccount == '全部'">
                    <tr
                      v-for="(account, index) in displayedDatas"
                      :key="index"
                      class="hover:bg-gray-200"
                      :class="[index % 2 != 0 ? 'bg-green-100' : '']"
                    >
                      <td class="px-2"><span v-html="account.日付"></span></td>
                      <td class="px-2"><span v-html="account.day"></span></td>
                      <td class="px-2">
                        {{ formatPrice(account.yahoo) }}
                      </td>
                      <td class="px-2">
                        {{ formatPrice(account.google) }}
                      </td>
                      <td class="px-2">
                        {{ formatPrice(account.実績合計) }}
                      </td>
                      <td class="px-2">
                        {{ formatPrice(account.予算) }}
                      </td>
                      <td class="px-2">
                        <span v-html="account.入電数"></span>
                      </td>
                      <td class="px-2">
                        {{ formatPrice(account.入電単価) }}
                      </td>
                    </tr>
                  </template>
                  <template v-else>
                    <tr
                      v-for="(account, index) in displayedDatas"
                      :key="index"
                      :class="[
                        index % 2 != 0
                          ? 'bg-green-100 hover:bg-gray-200 my-1'
                          : 'hover:bg-gray-200 my-1',
                      ]"
                    >
                      <td class="px-2"><span v-html="account.日付"></span></td>
                      <td class="px-2"><span v-html="account.day"></span></td>
                      <td class="px-2">
                        {{ formatPrice(account.yahoo) }}
                      </td>
                      <td class="px-2">
                        {{ formatPrice(account.google) }}
                      </td>
                      <td class="px-2">
                        {{ formatPrice(account.実績合計) }}
                      </td>
                      <td class="px-2">
                        <span v-html="account.予算消化率"></span>
                      </td>
                      <td class="px-2">
                        {{ formatPrice(account.予算) }}
                      </td>
                      <td class="px-2">
                        {{ formatPrice(account.予実) }}
                      </td>
                      <td class="px-2">
                        <span v-html="account.入電数"></span>
                      </td>
                      <td class="px-2">
                        {{ formatPrice(account.入電単価) }}
                      </td>
                    </tr>
                  </template>
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
    accounts: Array,
    table_header: Array,
    dailyDatas: Object,
  },
  data: () => ({
    selectedAccount: "全部",
    accountList: [],
    accountHeader: [
      "日付",
      "day",
      "yahoo",
      "google",
      "実績合計<br>(fee込み)",
      "予算消化率",
      "外部依頼予想<br>(fee込み)",
      "予実<br>差額",
      "入電数",
      "fee込み<br>入電コスト",
    ],
    startDate: "2021-05-01",
    endDate: "2021-05-07",
    page: 1,
    perPage: 16,
    pages: [],
  }),
  methods: {
    csvExport(arrData) {
      let csvContent = "data:text/csv;charset=utf-8,";
      csvContent += [
        Object.keys(arrData[0]).join(","),
        ...arrData.map((item) => Object.values(item).join(",")),
      ]
        .join("\r\n")
        .replace(/(^\[)|(\]$)/gm, "");

      const data = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", data);
      link.setAttribute("download", this.selectedAccount + "export.csv");
      link.click();
    },
    formatPrice(value) {
      const formatter = new Intl.NumberFormat("en-US", { style: "currency", currency: "JPY",
        maximumFractionDigits: 0,
      });
      return formatter.format(value);
    },
    getDayOfWeek(date) {
      const days = ["日", "月", "火", "水", "木", "金", "土"];
      const dt = new Date(date);
      return days[dt.getDay()];
    },
    classObject(name) {
      if (name === this.selectedAccount) {
        return "border-l-4 border-red-500";
      }
      return "";
    },
    toggleAcoount(name) {
      // console.log(name);
      this.selectedAccount = name;
      const accs = [];
      const selectedData = this.dailyDatas[this.selectedAccount];
      for (const [key, value] of Object.entries(selectedData)) {
        let dailysummary;
        if (this.selectedAccount == "合計") {
          dailysummary = {
            日付: value.date,
            day: this.getDayOfWeek(value.date),
            yahoo: value.yahooAds,
            google: value.googleAds,
            実績合計: value.total,
            予算: value.budget,
            入電数: value.numberOfCalls,
            入電単価: value.costPerCall,
          };
        } else {
          dailysummary = {
            日付: value.date,
            day: this.getDayOfWeek(value.date),
            yahoo: value.yahooAds,
            google: value.googleAds,
            実績合計: value.total,
            予算消化率: ((value.total / value.budget) * 100).toFixed(2) + "%",
            予算: value.budget,
            予実: (value.budget - value.total).toFixed(2),
            入電数: value.numberOfCalls,
            入電単価: value.costPerCall,
          };
        }
        accs.push(dailysummary);
      }
      this.accountList = accs;
    },
    setPages() {
      let numberOfPages = Math.ceil(this.accountList.length / this.perPage);
      if (this.pages.length == numberOfPages) {
        return 0;
      }
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
      return this.paginate(this.accountList);
    },
  },
  watch: {
    accountList() {
      this.setPages();
    },
  },
  mounted() {
    const accs = [];
    const selectedData = this.dailyDatas[this.selectedAccount];
    console.log(selectedData);
    const { startDate, endDate } = route().params;
    if (startDate && endDate) {
      this.startDate = startDate;
      this.endDate = endDate;
    } else {
      const dates = Object.keys(selectedData);
      this.startDate = dates[0];
      this.endDate = dates[dates.length - 1];
    }
    for (const [key, value] of Object.entries(selectedData)) {
      const dailysummary = {
        日付: value.date,
        day: this.getDayOfWeek(value.date),
        yahoo: +value.yahooAds,
        google: +value.googleAds,
        実績合計: +value.total,
        予算: +value.budget,
        入電数: value.numberOfCalls,
        入電単価: +value.costPerCall,
      };
      accs.push(dailysummary);
    }
    this.accountList = accs;
  },
};
</script>

<style>
</style>