import { computed, onBeforeUnmount, onMounted, ref } from "vue";

export const LANGUAGE_STORAGE_KEY = "achar_language";
export const LANGUAGE_CHANGED_EVENT = "achar:language-updated";
export const SUPPORTED_LANGUAGES = ["en", "km", "zh"];

function normalizeLanguage(value) {
  const raw = String(value || "").trim().toLowerCase();
  return SUPPORTED_LANGUAGES.includes(raw) ? raw : "en";
}

export function getStoredLanguage() {
  try {
    return normalizeLanguage(localStorage.getItem(LANGUAGE_STORAGE_KEY));
  } catch {
    return "en";
  }
}

export function setStoredLanguage(language) {
  const next = normalizeLanguage(language);
  try {
    localStorage.setItem(LANGUAGE_STORAGE_KEY, next);
  } catch {
    // Ignore storage failures and keep runtime language state.
  }
  window.dispatchEvent(new Event(LANGUAGE_CHANGED_EVENT));
  return next;
}

export function useLanguage() {
  const language = ref(getStoredLanguage());

  function syncLanguage() {
    language.value = getStoredLanguage();
  }

  onMounted(() => {
    window.addEventListener(LANGUAGE_CHANGED_EVENT, syncLanguage);
    window.addEventListener("storage", syncLanguage);
  });

  onBeforeUnmount(() => {
    window.removeEventListener(LANGUAGE_CHANGED_EVENT, syncLanguage);
    window.removeEventListener("storage", syncLanguage);
  });

  function updateLanguage(next) {
    language.value = setStoredLanguage(next);
  }

  return {
    language,
    updateLanguage,
  };
}

export function useLanguageCopy(copyByLanguage) {
  const { language, updateLanguage } = useLanguage();
  const uiText = computed(() => copyByLanguage[language.value] || copyByLanguage.en || {});

  return {
    language,
    updateLanguage,
    uiText,
  };
}
