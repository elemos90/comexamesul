<?php if (!empty($breadcrumbs)): ?>
<nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <li class="flex items-center">
                <?php if (!empty($crumb['url']) && $index < count($breadcrumbs) - 1): ?>
                    <a href="<?= htmlspecialchars($crumb['url']) ?>" class="hover:text-primary-600"><?= htmlspecialchars($crumb['label']) ?></a>
                    <span class="mx-2">/</span>
                <?php else: ?>
                    <span class="font-medium text-gray-700"><?= htmlspecialchars($crumb['label']) ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
<?php endif; ?>
