<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Upload Yahoo Data
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
      <form enctype="multipart/form-data" @submit.prevent="form.post(route('hourly-ads.savehourlyfromcsv'))">
        <div class="mt-4">
          <jet-label for="name" value="Account Name" />
          <select-input
            v-model="form.name"
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
        <div class="mt-4">
          <jet-label for="file" value="File" />
          <input
            type="file"
            id="file"
            name="file"
            @input="form.csvFile = $event.target.files[0]"
          />
        </div>
        <div class="flex items-center justify-end mt-4">
          <progress
            v-if="form.progress"
            :value="form.progress.percentage"
            max="100"
          >
            {{ form.progress.percentage }}%
          </progress>
          <jet-button
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
            type="submit"
          >
            Submit
          </jet-button>
        </div>
      </form>
    </div>
  </app-layout>
</template>

<script>
import { useForm } from "@inertiajs/inertia-vue3";
import AppLayout from "@/Layouts/AppLayout";

import JetButton from "@/Jetstream/Button";
import JetInput from "@/Jetstream/Input";
import JetLabel from "@/Jetstream/Label";
import JetValidationErrors from "@/Jetstream/ValidationErrors";
import SelectInput from "@/Components/SelectInput";
import { Inertia } from "@inertiajs/inertia";

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
      name: null,
      csvFile: null,
    });

    function submit() {
      if (form.type == "hourly") {
        // Inertia.post("/testJob/public/ads-accounts/savehourlyfromcsv", form);
        Inertia.post(route("ads-accounts.savehourlyfromcsv"), form);
      } else {
        // Inertia.post("/testJob/public/ads-accounts/savedailyfromcsv", form);
        Inertia.post(route("ads-accounts.savedailyfromcsv"), form);
      }
    }
    return { form, submit };
  },
};
</script>