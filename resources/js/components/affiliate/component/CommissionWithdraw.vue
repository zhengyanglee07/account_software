<template>
  <div>
    <div class="row withdraw-container">
      <span class="w-100">
        <div class="withdraw-label">Withdraw Amount</div>
        <div class="grey-text">(minimum USD 100)</div></span
      >
      <div class="withdraw-amount">
        <input type="number" name="" id="" class="form-control" />
      </div>
      <div class="row w-100">
        <button class="primary-square-button">Submit</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'commission-withdraw',
};
</script>

<style scoped lang="scss">

.withdraw-container{
    padding: 0 5px;
}
.withdraw-label {
  display: block;
    line-height: 1.5;
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.grey-text {
  color: grey;
}
.withdraw-label,
.grey-text {
  display: inline;
}

.withdraw-amount {
  padding: 10px 0;
  width: 50%;
  @media (max-width: $md-display){
      width: 100%;
  }
}
</style>
