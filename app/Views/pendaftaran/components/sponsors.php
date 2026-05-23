<?php if (get_all_sponsors() !== null) : ?>
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <h4 class="fw-bolder text-center">Terima kasih atas dukungannya !</h4>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <?php foreach (get_all_sponsors() as $key => $value) : ?>
                    <div class="col-md-3 col-lg-2 col-6 p-3">
                        <img src="<?= base_url('uploads/assets/' . get_sponsor($key)) ?>" alt="Sponsor Event" class="img-fluid">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>