pub mod components;
pub mod exceptions;

use ext_php_rs::prelude::*;

#[php_startup]
pub fn setup() {
    exceptions::build();
    components::build();
}

#[php_module]
pub fn get_module(module: ModuleBuilder) -> ModuleBuilder {
    components::register(module)
}
