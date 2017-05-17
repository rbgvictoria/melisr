<?php if ($this->session->flashdata('error')  ||
        $this->session->flashdata('warning') ||
        $this->session->flashdata('success')): ?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php if ($this->session->flashdata('error')): ?>
            <?php if (is_array($this->session->flashdata('error'))): ?>
            <?php foreach ($this->session->flashdata('error') as $error): ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <?=$error?>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <?=$this->session->flashdata('error')?>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($this->session->flashdata('warning')): ?>
            <?php if (is_array($this->session->flashdata('warning'))): ?>
            <?php foreach ($this->session->flashdata('warning') as $warning): ?>
            <div class="alert alert-warning alert-dismissible" role="alert">
                <?=$warning?>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="alert alert-warning alert-dismissible" role="alert">
                <?=$this->session->flashdata('warning')?>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                <?=$this->session->flashdata('success')?>
            </div>
            <?php endif; ?>
        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php endif; ?>