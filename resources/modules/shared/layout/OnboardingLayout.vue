<template>
  <div class="container col-md-9 my-20">
    <BaseCard style="padding: 0 20px">
      <div class="main-title">
        <img
          src="@shared/assets/media/hypershapes-logo.png"
          class="main-title__title"
          style="height: 56px; width: auto"
          alt="hypershapes-logo"
        >
      </div>
      <div
        class="container px-0"
        style="width: 100%"
      >
        <slot />
      </div>
    </BaseCard>
  </div>
</template>

<style lang="scss" scoped>
.main-title {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  width: 100%;
  margin-bottom: 20px;

  &__title {
    height: 50px;
  }
}
</style>
