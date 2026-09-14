<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' }, // check-in terpilih, format YYYY-MM-DD
  nights: { type: Number, required: true }, // jumlah malam, dikunci sesuai booking asli
  minDate: { type: String, required: true }, // YYYY-MM-DD, tanggal paling awal yang boleh dipilih
  originalStart: { type: String, default: '' }, // check-in booking saat ini (ditandai abu-abu)
  originalEnd: { type: String, default: '' }, // check-out booking saat ini
})

const emit = defineEmits(['update:modelValue'])

function parseDate(str) {
  return new Date(str + 'T00:00:00')
}

function formatDateValue(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

function isSameDay(a, b) {
  return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate()
}

// start inklusif, end eksklusif (hari checkout bukan malam menginap)
function isBetween(date, start, end) {
  return date >= start && date < end
}

const today = new Date()
today.setHours(0, 0, 0, 0)

const initialRef = props.modelValue
  ? parseDate(props.modelValue)
  : (props.originalStart ? parseDate(props.originalStart) : today)

const viewYear = ref(initialRef.getFullYear())
const viewMonth = ref(initialRef.getMonth()) // 0-11

const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']

function prevMonth() {
  if (viewMonth.value === 0) {
    viewMonth.value = 11
    viewYear.value -= 1
  } else {
    viewMonth.value -= 1
  }
}

function nextMonth() {
  if (viewMonth.value === 11) {
    viewMonth.value = 0
    viewYear.value += 1
  } else {
    viewMonth.value += 1
  }
}

const weeks = computed(() => {
  const firstOfMonth = new Date(viewYear.value, viewMonth.value, 1)
  const startOffset = firstOfMonth.getDay()
  const gridStart = new Date(firstOfMonth)
  gridStart.setDate(gridStart.getDate() - startOffset)

  const days = []
  for (let i = 0; i < 42; i++) {
    const d = new Date(gridStart)
    d.setDate(gridStart.getDate() + i)
    days.push(d)
  }

  const result = []
  for (let i = 0; i < 6; i++) {
    result.push(days.slice(i * 7, i * 7 + 7))
  }
  return result
})

const minDateObj = computed(() => parseDate(props.minDate))
const originalStartObj = computed(() => (props.originalStart ? parseDate(props.originalStart) : null))
const originalEndObj = computed(() => (props.originalEnd ? parseDate(props.originalEnd) : null))
const selectedStartObj = computed(() => (props.modelValue ? parseDate(props.modelValue) : null))
const selectedEndObj = computed(() => {
  if (!selectedStartObj.value) return null
  const d = new Date(selectedStartObj.value)
  d.setDate(d.getDate() + props.nights)
  return d
})

function dayState(date) {
  const inCurrentMonth = date.getMonth() === viewMonth.value
  const disabled = date < minDateObj.value
  const isOriginal = originalStartObj.value && originalEndObj.value && isBetween(date, originalStartObj.value, originalEndObj.value)
  const isSelected = selectedStartObj.value && selectedEndObj.value && isBetween(date, selectedStartObj.value, selectedEndObj.value)
  const isToday = isSameDay(date, today)
  return { inCurrentMonth, disabled, isOriginal, isSelected, isToday }
}

function dayClasses(date) {
  const s = dayState(date)
  return {
    'text-ink/20 pointer-events-none': !s.inCurrentMonth || s.disabled,
    'cursor-pointer hover:bg-sand': s.inCurrentMonth && !s.disabled && !s.isSelected,
    'bg-mist/25 text-ink/70': s.isOriginal && !s.isSelected,
    'bg-pine text-sand font-semibold': s.isSelected,
    'ring-1 ring-brass': s.isToday && !s.isSelected,
  }
}

function selectDate(date) {
  const s = dayState(date)
  if (s.disabled || !s.inCurrentMonth) return
  emit('update:modelValue', formatDateValue(date))
}
</script>

<template>
  <div class="border border-mist/30 rounded-xl p-3 bg-white select-none w-full max-w-xs">
    <div class="flex items-center justify-between mb-2">
      <button type="button" @click="prevMonth" class="w-7 h-7 flex items-center justify-center rounded hover:bg-sand text-ink/60">&lsaquo;</button>
      <p class="text-sm font-medium text-ink">{{ monthNames[viewMonth] }} {{ viewYear }}</p>
      <button type="button" @click="nextMonth" class="w-7 h-7 flex items-center justify-center rounded hover:bg-sand text-ink/60">&rsaquo;</button>
    </div>

    <div class="grid grid-cols-7 gap-1 text-center text-[11px] text-ink/40 mb-1">
      <span v-for="d in dayNames" :key="d">{{ d }}</span>
    </div>

    <div class="grid grid-cols-7 gap-1">
      <template v-for="(week, wi) in weeks" :key="wi">
        <button
          v-for="(date, di) in week"
          :key="di"
          type="button"
          @click="selectDate(date)"
          class="w-8 h-8 text-xs rounded-md flex items-center justify-center transition-colors"
          :class="dayClasses(date)"
        >
          {{ date.getDate() }}
        </button>
      </template>
    </div>

    <div class="flex items-center gap-3 mt-3 text-[11px] text-ink/50">
      <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-mist/25 inline-block"></span> Jadwal saat ini</span>
      <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-pine inline-block"></span> Jadwal baru</span>
    </div>
  </div>
</template>