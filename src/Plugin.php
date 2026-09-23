<?php

declare(strict_types=1);

namespace ClubCore;

use ClubCore\Application\Service\ImportService;
use ClubCore\Application\Service\SmsService;
use ClubCore\Application\Service\WooCommerceService;
use ClubCore\Application\UseCase\CreateMemberUseCase;
use ClubCore\Domain\Contract\AuditLoggerInterface;
use ClubCore\Domain\Contract\MemberRepositoryInterface;
use ClubCore\Domain\Contract\SmsLogRepositoryInterface;
use ClubCore\Domain\Contract\SmsProviderInterface;
use ClubCore\Infrastructure\Import\ImportProcessor;
use ClubCore\Infrastructure\Pattern\PatternParser;
use ClubCore\Infrastructure\Pattern\VariableRegistry;
use ClubCore\Infrastructure\Sms\FakeSmsProvider;
use ClubCore\Infrastructure\Sms\MelipayamakProvider;
use ClubCore\Infrastructure\WooCommerce\CustomerMatcher;
use ClubCore\Infrastructure\WooCommerce\WooCommerceDetector;
use ClubCore\Infrastructure\WooCommerce\WooCommerceIntegration;
use ClubCore\Presentation\Admin\AdminBootstrap;
use ClubCore\Infrastructure\WordPress\AuditLogRepository;
use ClubCore\Infrastructure\WordPress\CapabilityManager;
use ClubCore\Infrastructure\WordPress\MemberRepository;
use ClubCore\Infrastructure\WordPress\MigrationManager;
use ClubCore\Infrastructure\WordPress\SettingsManager;
use ClubCore\Infrastructure\WordPress\SmsLogRepository;

use ClubCore\Presentation\Terminal\TerminalManager;

/**
 * Main Plugin Container and Lifecycle Coordinator.
 *
 * @package ClubCore
 */
class Plugin
{
    private static ?self $instance = null;

    private MemberRepositoryInterface $memberRepository;
    private SmsLogRepositoryInterface $smsLogRepository;
    private AuditLoggerInterface $auditLogger;
    private SmsProviderInterface $smsProvider;
    private PatternParser $patternParser;
    private VariableRegistry $variableRegistry;
    private SmsService $smsService;
    private CreateMemberUseCase $createMemberUseCase;
    private ImportService $importService;
    private WooCommerceDetector $wcDetector;
    private WooCommerceService $wcService;
    private SettingsManager $settingsManager;
    private TerminalManager $terminalManager;

    private function __construct() {}

    public static function init(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->boot();
        }
        return self::$instance;
    }

    private function boot(): void
    {
        // 1. Settings & Migrations
        $this->settingsManager = new SettingsManager();
        $this->settingsManager->init();

        MigrationManager::checkAndMigrate();

        // 2. Repositories & Loggers
        $this->memberRepository = new MemberRepository();
        $this->smsLogRepository = new SmsLogRepository();
        $this->auditLogger = new AuditLogRepository();

        // 3. SMS Provider (Melipayamak or Fake for testing/staging)
        $authMode = (string) get_option('clubcore_sms_auth_mode', 'classic');
        $username = (string) get_option('clubcore_sms_username', '');
        $password = (string) get_option('clubcore_sms_password', '');
        $apiToken = (string) get_option('clubcore_sms_api_token', '');
        $timeout = (int) get_option('clubcore_sms_timeout', 30);

        if (defined('CLUBCORE_USE_FAKE_SMS') && CLUBCORE_USE_FAKE_SMS) {
            $this->smsProvider = new FakeSmsProvider();
        } else {
            $this->smsProvider = new MelipayamakProvider(
                username: $username,
                password: $password,
                apiToken: ($authMode === 'console') ? $apiToken : '',
                timeout: $timeout
            );
        }

        // 4. Pattern & Variable Registry
        $this->variableRegistry = new VariableRegistry();
        $this->patternParser = new PatternParser($this->variableRegistry);

        // 5. Domain Services & Use Cases
        $this->smsService = new SmsService(
            provider: $this->smsProvider,
            parser: $this->patternParser,
            registry: $this->variableRegistry,
            logRepository: $this->smsLogRepository,
            auditLogger: $this->auditLogger
        );

        $this->createMemberUseCase = new CreateMemberUseCase(
            $this->memberRepository,
            $this->auditLogger
        );

        // 6. Import/Export
        $importProcessor = new ImportProcessor(
            $this->memberRepository,
            $this->createMemberUseCase,
            $this->auditLogger
        );
        $this->importService = new ImportService(
            $importProcessor,
            $this->memberRepository,
            $this->auditLogger
        );

        // 7. WooCommerce Layer
        $this->wcDetector = new WooCommerceDetector();
        $customerMatcher = new CustomerMatcher();
        $this->wcService = new WooCommerceService($this->wcDetector, $customerMatcher);

        if ($this->wcDetector->isActive()) {
            $wcIntegration = new WooCommerceIntegration($this->wcDetector, $this->createMemberUseCase);
            $wcIntegration->init();
        }

        // 8. Kiosk / Mobile POS Terminal
        $this->terminalManager = new TerminalManager(
            $this->createMemberUseCase,
            $this->smsService
        );
        $this->terminalManager->init();

        // 9. Admin UI
        if (is_admin()) {
            $admin = new \ClubCore\Presentation\Admin\AdminBootstrap();
            $admin->init();
        }

        // 10. Register Export / Template Download action hooks
        add_action('admin_post_clubcore_export_members', [$this, 'handleExportRequest']);
        add_action('admin_post_clubcore_download_template', [$this, 'handleDownloadTemplateRequest']);
    }

    // ─── Service Accessors ───────────────────────────────────

    public function getMemberRepository(): MemberRepositoryInterface { return $this->memberRepository; }
    public function getSmsLogRepository(): SmsLogRepositoryInterface { return $this->smsLogRepository; }
    public function getAuditLogger(): AuditLoggerInterface { return $this->auditLogger; }
    public function getSmsProvider(): SmsProviderInterface { return $this->smsProvider; }
    public function getSmsService(): SmsService { return $this->smsService; }
    public function getCreateMemberUseCase(): CreateMemberUseCase { return $this->createMemberUseCase; }
    public function getImportService(): ImportService { return $this->importService; }
    public function getWooCommerceService(): WooCommerceService { return $this->wcService; }
    public function getTerminalManager(): TerminalManager { return $this->terminalManager; }
    public function getVersion(): string { return defined('CLUBCORE_VERSION') ? CLUBCORE_VERSION : '1.0.0'; }

    // ─── Direct Admin POST Handlers ─────────────────────────

    public function handleExportRequest(): void
    {
        check_admin_referer('clubcore_export');

        if (!current_user_can('clubcore_export_members')) {
            wp_die(esc_html__('شما دسترسی خروجی گرفتن از اعضا را ندارید.', 'clubcore'));
        }

        $format = sanitize_text_field($_GET['format'] ?? 'csv');
        $this->importService->export($format);
    }

    public function handleDownloadTemplateRequest(): void
    {
        check_admin_referer('clubcore_template');

        if (!current_user_can('clubcore_import_members')) {
            wp_die(esc_html__('شما دسترسی لازم را ندارید.', 'clubcore'));
        }

        $this->importService->downloadTemplate();
    }
}
