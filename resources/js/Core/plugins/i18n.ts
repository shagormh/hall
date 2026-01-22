import { createI18n } from "vue-i18n";
import en from '@/Lang/en.json';
import bn from '@/Lang/bn.json';


const i18n = createI18n({
    legacy: false,
    locale: localStorage.getItem('locale') || "en",
    globalInjection: true,
    messages: {
        en,
        bn
    }
});

export default i18n;
