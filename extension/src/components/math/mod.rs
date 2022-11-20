use ext_php_rs::prelude::ModuleBuilder;

pub mod functions;

pub fn register(module: ModuleBuilder) -> ModuleBuilder {
    functions::register(module)
}
