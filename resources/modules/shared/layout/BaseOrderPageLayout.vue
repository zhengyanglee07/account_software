<template>
  <slot name="header" />
  <div class="row">
    <div class="col-lg-8">
      <slot name="left" />
    </div>
    <div class="col-lg-4">
      <slot name="right" />
    </div>
  </div>
  <div class="mt-6 mb-10">
    <slot name="bottom" />
  </div>

  <div class="d-flex justify-content-end align-items-center mt-6 mb-10">
    <slot name="footer" />
  </div>
</template>

<script></script>
