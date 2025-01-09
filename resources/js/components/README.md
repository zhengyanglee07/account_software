## Table of Contents

- [Currency Select](#currency-select-global)

## Currency Select `Global`

![Currency Select](/docs/img/currency-select.png)



### Example Usage

```
<CurrencySelect v-model="selectedCurrency" />

```




### Props

#### a. modelValue `type: String` `default: 'MYR'`

To bind selected options for currency

---

#### b. options`type: Array` `default: null`

Overwrite default currency list (path: resources/js/lib/currencies.js)

---

#### c. showFlag`type: Boolean` `default: true`

To determine show currency flag or not.

---

#### d. showName`type: Boolean` `default: false`

To show name of currency ( EXP: MYR -  Malaysian Ringgit)

---

#### e. showSymbol`type: Boolean` `default: false`

To show symbol of currency ( EXP: MYR (RM) )

