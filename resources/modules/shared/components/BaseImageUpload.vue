<template>
	<!--begin::Image input-->
	<div
	class="image-input image-input-outline"
	data-kt-image-input="true"
	style="background-image: url()"
	>
		<!--begin::Preview existing avatar-->
		<div
			class="image-input-wrapper w-125px h-125px"
			:style="`background-image: url()`"
		></div>
		<!--end::Preview existing avatar-->

		<!--begin::Label-->
		<label
			class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-white shadow"
			data-kt-image-input-action="change"
			data-bs-toggle="tooltip"
			title="Change avatar"
		>
			<i class="fas fa-pen ffs-7"></i>

			<!--begin::Inputs-->
			<input type="file" name="avatar" accept=".png, .jpg, .jpeg" />
			<input type="hidden" name="avatar_remove" />
			<!--end::Inputs-->
		</label>
		<!--end::Label-->

		<!--begin::Remove-->
		<span
			class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-white shadow"
			data-kt-image-input-action="remove"
			data-bs-toggle="tooltip"
			@click="removeImage()"
			title="Remove avatar"
		>
			<i class="fas fa-x ffs-2"></i>
		</span>
		<!--end::Remove-->
	</div>
	<!--end::Image input-->
</template>

<script>

export default {

}
</script>

<style>

</style>