import 'runtime_config.dart';

class PusherConfig {
  static const String _defaultAppKey = '8c80725adade23c572e4';
  static const String _defaultCluster = 'mt1';

  static String get appKey {
    final env = const String.fromEnvironment('PUSHER_APP_KEY').trim();
    if (env.isNotEmpty) return env;
    final runtime = RuntimeConfig.pusherAppKey.trim();
    if (runtime.isNotEmpty) return runtime;
    return _defaultAppKey;
  }

  static String get cluster {
    final env = const String.fromEnvironment('PUSHER_CLUSTER').trim();
    if (env.isNotEmpty) return env;
    final runtime = RuntimeConfig.pusherCluster.trim();
    if (runtime.isNotEmpty) return runtime;
    return _defaultCluster;
  }

  static String get authEndpoint =>
      const String.fromEnvironment('PUSHER_AUTH_ENDPOINT').trim();
  static const String channelPrefix = String.fromEnvironment(
    'PUSHER_CHANNEL_PREFIX',
    defaultValue: 'private-user.',
  );

  static String get notificationsChannel {
    final env = const String.fromEnvironment('PUSHER_NOTIFICATIONS_CHANNEL').trim();
    if (env.isNotEmpty) return env;
    return RuntimeConfig.pusherNotificationsChannel.trim();
  }

  static bool get useTls {
    final env = const bool.fromEnvironment('PUSHER_USE_TLS', defaultValue: true);
    return env && RuntimeConfig.pusherUseTls;
  }

  static bool get isConfigured {
    final normalizedKey = appKey.trim().toUpperCase();
    final normalizedCluster = cluster.trim().toUpperCase();

    if (normalizedKey.isEmpty || normalizedCluster.isEmpty) return false;
    if (normalizedKey == 'TU_KEY' || normalizedCluster == 'TU_CLUSTER') return false;
    return true;
  }

  static String get notConfiguredReason {
    final key = appKey.trim();
    final cl = cluster.trim();
    if (key.isEmpty && cl.isEmpty) return 'Faltan PUSHER_APP_KEY y PUSHER_CLUSTER';
    if (key.isEmpty) return 'Falta PUSHER_APP_KEY';
    if (cl.isEmpty) return 'Falta PUSHER_CLUSTER';
    if (key.toUpperCase() == 'TU_KEY') return 'PUSHER_APP_KEY sigue en TU_KEY';
    if (cl.toUpperCase() == 'TU_CLUSTER') return 'PUSHER_CLUSTER sigue en TU_CLUSTER';
    return 'Config inválida';
  }

  static String userChannel(int userId) => '$channelPrefix$userId';
}
