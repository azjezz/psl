use ext_php_rs::prelude::ModuleBuilder;

mod helpers;
mod macros;

pub mod channel;
pub mod ds;
pub mod math;

pub fn register(mut module: ModuleBuilder) -> ModuleBuilder {
    module = math::register(module);

    module
}

pub fn build() {
    ds::build();
    channel::build();
}
