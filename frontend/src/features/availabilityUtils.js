function seededNumber(input) {
  let hash = 0
  for (let i = 0; i < input.length; i += 1) {
    hash = (hash * 31 + input.charCodeAt(i)) % 100000
  }
  return hash
}

export function isDateLikelyBooked(dateIso) {
  return seededNumber(dateIso) % 7 === 0
}

export function isSlotLikelyBooked(dateIso, slotValue) {
  return seededNumber(`${dateIso}-${slotValue}`) % 6 === 0
}
