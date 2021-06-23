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
          <div class="flex w-full h-18 space-x-4 justify-end pr-4">
            <div class="mb-2">
              <label
                class="block text-gray-700 text-sm font-bold mb-2"
                for="username"
              >
                Select Platform
              </label>
              <select
                class="border rounded w-full py-2 px-3 text-gray-700"
                v-model="platform"
                @change="onChange($event)"
              >
                <option>all</option>
                <option>google</option>
                <option>yahoo</option>
              </select>
            </div>
            <div class="mb-2">
              <label
                class="block text-gray-700 text-sm font-bold mb-2"
                for="username"
              >
                Select Prefecture
              </label>
              <select
                v-model="prefecture"
                @change="onPrefectureChange($event)"
                class="border rounded w-full py-2 px-3 text-gray-700"
              >
                <option>all</option>
                <option
                  v-for="(value, name, index) in aqlHourlyData.prefectures"
                  :key="index"
                >
                  {{ name }}
                </option>
              </select>
            </div>
            <div class="mb-2">
              <label
                class="block text-gray-700 text-sm font-bold mb-2"
                for="username"
              >
                Select Request type
              </label>
              <select v-model="type" @change="onTypeChange($event)">
                <option>all</option>
                <option
                  v-for="(value, name, index) in aqlHourlyData.requestTypes"
                  :key="index"
                >
                  {{ name }}
                </option>
              </select>
            </div>

            <button
              class="
                mt-6
                h-12
                inline-block
                bg-green-500
                hover:bg-green-400
                focus:outline-none
                focus:ring
                focus:ring-offset-2
                focus:ring-green-500
                focus:ring-opacity-50
                text-white
                p-3
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
              @click="csvExport(formatedListForCsv)"
            >
              Download CSV
            </button>
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
                      @click="sortByColumn(index)"
                      class="
                        border
                        mx-1
                        border-r-1 border-red-100
                        bg-green-600
                        text-white
                      "
                    >
                      <span class="text-xs font-light" v-html="header"></span>
                      <svg
                        v-if="sortColumn == header && sortOrder == 1"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          class="text-xs font-light"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 9l-7 7-7-7"
                        />
                      </svg>
                      <svg
                        v-if="sortColumn == header && sortOrder == -1"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M5 15l7-7 7 7"
                        />
                      </svg>
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
                      <span
                        class="text-sm font-light"
                        v-html="account.時間"
                      ></span>
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.入電数"
                      ></span>
                    </td>
                    <td class="px-1 text-sm font-light">
                      {{ formatPrice(account.入電単価) }}
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.表示回数"
                      ></span>
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.クリック数"
                      ></span>
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.クリック率"
                      ></span
                      >%
                    </td>
                    <td class="px-1 text-sm font-light">
                      {{ formatPrice(account.クリック単価) }}
                    </td>
                    <td class="px-1 text-sm font-light">
                      {{ formatPrice(account.費用) }}
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.コンバージョン"
                      ></span>
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.コンバージョン率"
                      ></span
                      >%
                    </td>
                    <td class="px-1 text-sm font-light">
                      {{ formatPrice(account.コンバージョン単価) }}
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
    aqlHourlyData: Object,
    table_header: Array,
    hourlyDatas: Object,
  },
  data: () => ({
    selectedAccount: "全部",
    accountList: [],
    formatedListForCsv: [],
    platform: "all",
    date: "",
    prefecture: "all",
    type: "all",
    page: 1,
    perPage: 12,
    pages: [],
    sortColumn: "",
    sortOrder: 0,
  }),
  methods: {
    formatPrice(value) {
      const formatter = new Intl.NumberFormat("en-US", { style: "currency", currency: "JPY",
        maximumFractionDigits: 0,
      });
      return formatter.format(value);
    },
    sortByColumn(event) {
      if (this.sortColumn == this.table_header[event]) {
        if (this.sortOrder == 0) {
          this.sortOrder = 1;
        } else if (this.sortOrder == 1) {
          this.sortOrder = -1;
        } else {
          this.sortOrder = 0;
          this.sortColumn = "";
        }
      } else {
        this.sortColumn = this.table_header[event];
        this.sortOrder = 1;
      }
    },
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
      link.setAttribute(
        "download",
        this.selectedAccount + " " + this.date + " export.csv"
      );
      link.click();
    },
    onChange(event) {
      console.log(event.target.value);
      let data = route("sss-detail-exp", {
        date: this.table_header[0],
        adsPlatform: event.target.value,
      });
      if (event.target.value == "all") {
        data = route("sss-detail-exp", {
          date: this.table_header[0],
        });
      }
      const link = document.createElement("a");
      link.setAttribute("href", data);
      link.click();
    },
    onPrefectureChange(event) {
      console.log(event.target.value);
      let data = route("sss-detail-exp", {
        date: this.table_header[0],
        prefecture: event.target.value,
      });
      if (event.target.value == "all") {
        data = route("sss-detail-exp", {
          date: this.table_header[0],
        });
      }
      const link = document.createElement("a");
      link.setAttribute("href", data);
      link.click();
    },
    onTypeChange(event) {
      console.log(event.target.value);
      let data = route("sss-detail-exp", {
        date: this.table_header[0],
        type: event.target.value,
      });
      if (event.target.value == "all") {
        data = route("sss-detail-exp", {
          date: this.table_header[0],
        });
      }
      const link = document.createElement("a");
      link.setAttribute("href", data);
      link.click();
    },
    toggleAcoount(name) {
      console.log(name);
      this.selectedAccount = name;
      const selectedData = (this.accountList =
        this.hourlyDatas[this.selectedAccount]);
      let accs = [];
      for (const [key, value] of Object.entries(selectedData)) {
        const dailyfomatedForcsv = {
          時間: value.時間,
          入電数: value.入電数,
          入電単価: "¥" + value.入電単価,
          表示回数: value.表示回数,
          クリック数: value.クリック数,
          クリック率: value.クリック率 + "%",
          クリック単価: "¥" + value.クリック単価,
          費用: "¥" + value.費用,
          コンバージョン: value.コンバージョン,
          コンバージョン率: value.コンバージョン率 + "%",
          コンバージョン単価: "¥" + value.コンバージョン単価,
        };
        accs.push(dailyfomatedForcsv);
      }
      this.formatedListForCsv = accs;
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
      let dailyList = [...this.accountList];
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
    console.log(this.hourlyDatas);
    const { date, type, adsPlatform, prefecture } = route().params;
    const selectedData = this.hourlyDatas[this.selectedAccount];
    if (adsPlatform) {
      this.platform = adsPlatform;
    } else if (type) {
      this.type = type;
    } else if (prefecture) {
      this.prefecture = prefecture;
    }
    if (date) {
      this.date = date;
    }
    this.accountList = selectedData;
    let accs = [];
    let count = 0;
    const sumOfData = {
      時間:'all',
      入電数: 0,
      入電単価: 0,
      表示回数: 0,
      クリック数: 0,
      クリック率: 0,
      クリック単価: 0,
      費用: 0,
      コンバージョン: 0,
      コンバージョン率: 0,
      コンバージョン単価: 0,
    };
    for (const [key, value] of Object.entries(selectedData)) {
      sumOfData.入電数+=value.入電数;
      sumOfData.入電単価+=value.入電単価;
      sumOfData.表示回数+=value.表示回数;
      sumOfData.クリック数+=value.クリック数;
      sumOfData.クリック率+=value.クリック率;
      sumOfData.クリック単価+=value.クリック単価;
      sumOfData.費用+=Math.round(value.費用);
      sumOfData.コンバージョン+=value.コンバージョン;
      sumOfData.コンバージョン率+=value.コンバージョン率;
      sumOfData.コンバージョン単価+=value.コンバージョン単価;
      count++;
      const dailyfomatedForcsv = {
        時間: value.時間,
        入電数: value.入電数,
        入電単価: "¥" + value.入電単価,
        表示回数: value.表示回数,
        クリック数: value.クリック数,
        クリック率: value.クリック率 + "%",
        クリック単価: "¥" + value.クリック単価,
        費用: "¥" + value.費用,
        コンバージョン: value.コンバージョン,
        コンバージョン率: value.コンバージョン率 + "%",
        コンバージョン単価: "¥" + value.コンバージョン単価,
      };
      accs.push(dailyfomatedForcsv);
    }
    sumOfData.入電単価=sumOfData.入電単価/count;
    sumOfData.クリック率=(sumOfData.クリック率/count).toFixed(2)+ "%";
    sumOfData.クリック単価=sumOfData.クリック単価/count;
    sumOfData.コンバージョン率=(sumOfData.コンバージョン率/count).toFixed(2)+ "%";
    sumOfData.コンバージョン単価=(sumOfData.コンバージョン単価/count).toFixed(2);
    accs.push(sumOfData);
    this.formatedListForCsv = accs;
  },
};
</script>
