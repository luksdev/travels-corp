<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="cn(
          'w-full justify-start text-left font-normal rounded-md',
          !value && 'text-muted-foreground',
        )"
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        {{ value ? df.format(value.toDate(getLocalTimeZone())) : placeholder }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0" align="start">
      <Calendar v-model="value" initial-focus @update:model-value="handleDateChange" />
    </PopoverContent>
  </Popover>
</template>

<script setup lang="ts">
import type { DateValue } from "@internationalized/date"
import {
  DateFormatter,
  getLocalTimeZone,
  CalendarDate
} from "@internationalized/date"
import { CalendarIcon } from "lucide-vue-next"
import { ref, watch } from "vue"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"

interface Props {
  modelValue?: Date | null
  placeholder?: string
  disabled?: boolean
  minDate?: Date
  maxDate?: Date
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Selecione uma data',
  disabled: false
})

const emit = defineEmits<{
  'update:modelValue': [value: Date | null]
}>()

const df = new DateFormatter("pt-BR", {
  dateStyle: "long",
})

const value = ref<DateValue>()

const dateToDateValue = (date: Date | null): DateValue | undefined => {
  if (!date) return undefined
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  return new CalendarDate(year, month, day)
}

const dateValueToDate = (dateValue: DateValue | undefined): Date | null => {
  if (!dateValue) return null
  return dateValue.toDate(getLocalTimeZone())
}

watch(() => props.modelValue, (newValue) => {
  value.value = dateToDateValue(newValue || null)
}, { immediate: true })

const handleDateChange = (dateValue: DateValue | undefined) => {
  value.value = dateValue
  const jsDate = dateValueToDate(dateValue)
  emit('update:modelValue', jsDate)
}
</script>