<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Add New Yahoo Daily Ads Data
      </h2>
    </template>

    <div
      class="
        min-h-screen
        px-8
        py-12
        max-w-md
        mx-auto
        sm:max-w-xl
        bg-gray-100
        p-0
        sm:p-8
      "
    >
      <jet-validation-errors class="mb-4" />

      <form @submit.prevent="form.post(route('daily-ads.store'))">
        <div class="mt-4">
          <jet-label for="name" value="Account Name" />
          <select-input
            v-model="form.adsAccountId"
            :error="form.errors.name"
            class="
              mt-1
              block
              w-full
              border-gray-300
              focus:border-indigo-300
              focus:ring focus:ring-indigo-200 focus:ring-opacity-50
              rounded-md
              shadow-sm
            "
          >
            <option :value="null">Select a Account</option>
            <option
              v-for="(account, index) in yahooAccounts"
              :key="index"
              :value="account.id"
            >
              {{ account.name }}
            </option>
          </select-input>
        </div>
        <div>
          <jet-label for="date" value="Date" />
          <input
            class="
              border border-grey-300
              rounded-lg
              shadow-sm
              mr-2
              mt-1
              block
              w-full
            "
            type="date"
            v-model="form.date"
            name="date"
            id="date"
          />
        </div>
        <div>
          <jet-label for="clicks" value="Clicks" />
          <jet-input
            id="clicks"
            type="number"
            class="mt-1 block w-full"
            v-model="form.clicks"
            required
            autofocus
            autocomplete="clicks"
          />
        </div>
        <div class="mt-4">
          <jet-label for="impressions" value="Impressions" />
          <jet-input
            id="impressions"
            type="text"
            class="mt-1 block w-full"
            v-model="form.impressions"
            required
            autocomplete="impressions"
          />
        </div>
        <div class="mt-4">
          <jet-label for="ctr" value="CTR" />
          <jet-input
            id="ctr"
            type="text"
            class="mt-1 block w-full"
            v-model="form.ctr"
            required
            autocomplete="ctr"
          />
        </div>
        <div class="mt-4">
          <jet-label for="cost" value="cost" />
          <jet-input
            id="cost"
            type="text"
            class="mt-1 block w-full"
            v-model="form.cost"
            required
            autocomplete="cost"
          />
        </div>
        <div class="mt-4">
          <jet-label for="cpc" value="CPC" />
          <jet-input
            id="cpc"
            type="text"
            class="mt-1 block w-full"
            v-model="form.cpc"
            required
            autocomplete="CPC"
          />
        </div>
        <div class="mt-4">
          <jet-label for="conversions" value="Conversions" />
          <jet-input
            id="conversions"
            type="number"
            class="mt-1 block w-full"
            v-model="form.conversions"
            required
            autocomplete="Conversions"
          />
        </div>
        <div class="mt-4">
          <jet-label for="conversions_rate" value="Conversions Rate" />
          <jet-input
            id="conversions_rate"
            type="text"
            class="mt-1 block w-full"
            v-model="form.conversions_rate"
            required
            autocomplete="Conversions Rate"
          />
        </div>
        <div class="mt-4">
          <jet-label for="cost_per_conversion" value="Cost Per Conversion" />
          <jet-input
            id="cost_per_conversion"
            type="number"
            class="mt-1 block w-full"
            v-model="form.cost_per_conversion"
            required
            autocomplete="Cost Per Conversion"
          />
        </div>
        <div class="flex items-center justify-end mt-4">
          <jet-button
            class="ml-4"
          >
            Save
          </jet-button>
          <!-- <jet-button
            class="ml-4"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Save
          </jet-button> -->
        </div>
      </form>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import { useForm } from "@inertiajs/inertia-vue3";

import JetButton from "@/Jetstream/Button";
import JetInput from "@/Jetstream/Input";
import JetLabel from "@/Jetstream/Label";
import JetValidationErrors from "@/Jetstream/ValidationErrors";
import SelectInput from "@/Components/SelectInput";
export default {
  components: {
    AppLayout,
    JetButton,
    JetInput,
    SelectInput,
    JetLabel,
    JetValidationErrors,
  },
  props: {
    yahooAccounts: {
      type: Array,
      required: true,
    },
  },
  setup() {
    const form = useForm({
      date: "",
      clicks: "",
      adsAccountId: "",
      impressions: "",
      ctr: "",
      cost: "",
      cpc: "",
      conversions: "",
      conversions_rate: "",
      cost_per_conversion: "",
    });
    return { form };
  },
  methods: {
    submit() {
      //   console.log(this.form);
      this.form.post(this.route("ads-accounts.store"));
    },
  },
};
</script>